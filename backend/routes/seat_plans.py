from datetime import datetime, timezone

from flask import Blueprint, jsonify, request
from pymongo import DESCENDING

from extensions import get_db
from seat_algorithm import Student, generate_seating, natural_sort_key
from utils import error, serialize_doc, serialize_many, to_object_id

seat_plans_bp = Blueprint("seat_plans", __name__, url_prefix="/api/seat-plans")


@seat_plans_bp.get("")
def list_seat_plans():
    db = get_db()
    plans = list(db.seat_plans.find().sort("created_at", DESCENDING))
    return jsonify(serialize_many(plans))


@seat_plans_bp.get("/<plan_id>")
def get_seat_plan(plan_id):
    db = get_db()
    oid = to_object_id(plan_id)
    if oid is None:
        return error("Invalid seat plan id", 400)

    plan = db.seat_plans.find_one({"_id": oid})
    if not plan:
        return error("Seat plan not found", 404)
    return jsonify(serialize_doc(plan))


@seat_plans_bp.post("/generate")
def generate_seat_plan():
    db = get_db()
    data = request.get_json(silent=True) or {}
    room_id = data.get("room_id")
    course_ids = data.get("course_ids")
    num_students = data.get("num_students")

    if not room_id or not isinstance(course_ids, list) or not course_ids:
        return error("room_id and a non-empty course_ids list are required")

    room_oid = to_object_id(room_id)
    if room_oid is None:
        return error("Invalid room_id", 400)
    room = db.rooms.find_one({"_id": room_oid})
    if not room:
        return error("Room not found", 404)

    course_oids = []
    for cid in course_ids:
        oid = to_object_id(cid)
        if oid is None:
            return error(f"Invalid course_id: {cid}", 400)
        course_oids.append(oid)

    courses = {c["_id"]: c for c in db.courses.find({"_id": {"$in": course_oids}})}
    missing = [str(cid) for cid in course_oids if cid not in courses]
    if missing:
        return error(f"Unknown course_id(s): {', '.join(missing)}", 404)

    student_docs = list(db.students.find({"course_id": {"$in": course_oids}}))
    if not student_docs:
        return error("No students found for selected courses")

    if isinstance(num_students, int) and num_students > 0:
        student_docs.sort(key=lambda s: natural_sort_key(s["exam_roll"]))
        student_docs = student_docs[:num_students]

    students = [
        Student(
            exam_roll=s["exam_roll"],
            name=s["name"],
            course_id=str(s["course_id"]),
            course_name=courses[s["course_id"]]["name"],
        )
        for s in student_docs
    ]

    result = generate_seating(students, room["rows"], room["cols"])

    grid = [
        [
            None
            if cell is None
            else {
                "exam_roll": cell.exam_roll,
                "name": cell.name,
                "course_id": cell.course_id,
                "course_name": cell.course_name,
            }
            for cell in row
        ]
        for row in result.grid
    ]

    plan_doc = {
        "room_id": room_oid,
        "room_no": room["room_no"],
        "rows": room["rows"],
        "cols": room["cols"],
        "grid": grid,
        "conflicts": result.conflicts,
        "unplaced_count": len(result.unplaced),
        "unplaced_rolls": [s.exam_roll for s in result.unplaced],
        "course_ids": course_oids,
        "created_at": datetime.now(timezone.utc),
    }
    insert_result = db.seat_plans.insert_one(plan_doc)
    plan_doc["_id"] = insert_result.inserted_id

    return jsonify(serialize_doc(plan_doc)), 201


@seat_plans_bp.delete("/<plan_id>")
def delete_seat_plan(plan_id):
    db = get_db()
    oid = to_object_id(plan_id)
    if oid is None:
        return error("Invalid seat plan id", 400)

    result = db.seat_plans.delete_one({"_id": oid})
    if result.deleted_count == 0:
        return error("Seat plan not found", 404)
    return "", 204
