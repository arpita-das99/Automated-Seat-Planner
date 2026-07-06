import mongomock
import pytest

import extensions
from app import create_app
from config import Config


class TestConfig(Config):
    FRONTEND_ORIGIN = "http://localhost:5173"


@pytest.fixture
def client(monkeypatch):
    # Patch init_db so create_app() wires up a mongomock database instead of
    # a real MongoClient connection.
    fake_client = mongomock.MongoClient()
    fake_db = fake_client["seat_planner_test"]

    def fake_init_db(app):
        return extensions.set_db(fake_db)

    monkeypatch.setattr(extensions, "init_db", fake_init_db)
    monkeypatch.setattr("app.init_db", fake_init_db)

    app = create_app(TestConfig)
    app.testing = True
    with app.test_client() as c:
        yield c


def test_full_flow_room_courses_students_generate_updates_filled_seats(client):
    # 1. Create a room
    resp = client.post("/api/rooms", json={"room_no": "R101", "rows": 4, "cols": 4})
    assert resp.status_code == 201
    room = resp.get_json()
    assert room["filled_seats"] == 0
    room_id = room["_id"]

    # 2. Create four courses (so zero-conflict placement is achievable)
    course_ids = []
    for name in ["Math", "Physics", "Chemistry", "Biology"]:
        resp = client.post("/api/courses", json={"name": name, "batch": "2026"})
        assert resp.status_code == 201
        course_ids.append(resp.get_json()["_id"])

    # 3. Bulk import students, 4 per course = 16 total (fills the room exactly)
    students_payload = []
    for idx, cid in enumerate(course_ids):
        for i in range(4):
            students_payload.append(
                {"exam_roll": f"C{idx}-{i}", "name": f"Student {idx}-{i}", "course_id": cid}
            )
    resp = client.post("/api/students/bulk", json={"students": students_payload})
    assert resp.status_code == 200
    body = resp.get_json()
    assert body["inserted"] == 16
    assert body["skipped"] == 0

    # Re-importing the same batch should skip all as duplicates.
    resp = client.post("/api/students/bulk", json={"students": students_payload})
    assert resp.get_json() == {"inserted": 0, "skipped": 16}

    # 4. Generate a seat plan
    resp = client.post(
        "/api/seat-plans/generate", json={"room_id": room_id, "course_ids": course_ids}
    )
    assert resp.status_code == 201
    plan = resp.get_json()
    assert plan["room_id"] == room_id
    assert plan["unplaced_count"] == 0
    assert plan["conflicts"] == 0  # 4 courses fill the room -> zero should be achievable

    # 5. Confirm room's filled_seats reflects the new plan
    resp = client.get("/api/rooms")
    assert resp.status_code == 200
    rooms = resp.get_json()
    updated_room = next(r for r in rooms if r["_id"] == room_id)
    assert updated_room["filled_seats"] == 16


def test_duplicate_room_no_rejected(client):
    client.post("/api/rooms", json={"room_no": "R1", "rows": 2, "cols": 2})
    resp = client.post("/api/rooms", json={"room_no": "R1", "rows": 3, "cols": 3})
    assert resp.status_code == 409


def test_duplicate_exam_roll_rejected(client):
    resp = client.post("/api/courses", json={"name": "Math", "batch": "2026"})
    course_id = resp.get_json()["_id"]

    resp = client.post(
        "/api/students", json={"exam_roll": "X1", "name": "A", "course_id": course_id}
    )
    assert resp.status_code == 201

    resp = client.post(
        "/api/students", json={"exam_roll": "X1", "name": "B", "course_id": course_id}
    )
    assert resp.status_code == 409


def test_unknown_course_id_rejected(client):
    resp = client.post(
        "/api/students",
        json={"exam_roll": "X2", "name": "A", "course_id": "000000000000000000000000"},
    )
    assert resp.status_code == 404


def test_delete_course_with_students_blocked(client):
    resp = client.post("/api/courses", json={"name": "Math", "batch": "2026"})
    course_id = resp.get_json()["_id"]
    client.post(
        "/api/students", json={"exam_roll": "X3", "name": "A", "course_id": course_id}
    )

    resp = client.delete(f"/api/courses/{course_id}")
    assert resp.status_code == 409


def test_generate_with_infeasible_room_reports_unplaced_without_crash(client):
    resp = client.post("/api/rooms", json={"room_no": "Tiny", "rows": 2, "cols": 2})
    room_id = resp.get_json()["_id"]

    resp = client.post("/api/courses", json={"name": "Math", "batch": "2026"})
    course_id = resp.get_json()["_id"]

    students_payload = [
        {"exam_roll": f"Y{i}", "name": f"S{i}", "course_id": course_id} for i in range(10)
    ]
    client.post("/api/students/bulk", json={"students": students_payload})

    resp = client.post(
        "/api/seat-plans/generate", json={"room_id": room_id, "course_ids": [course_id]}
    )
    assert resp.status_code == 201
    plan = resp.get_json()
    assert plan["unplaced_count"] == 6  # 10 students - 4 seats
