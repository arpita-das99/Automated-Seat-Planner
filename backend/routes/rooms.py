from flask import Blueprint, jsonify, request
from pymongo import DESCENDING
from pymongo.errors import DuplicateKeyError

from extensions import get_db
from utils import error, serialize_many, to_object_id

rooms_bp = Blueprint("rooms", __name__, url_prefix="/api/rooms")


def _filled_seats(db, room_id):
    latest_plan = db.seat_plans.find_one(
        {"room_id": room_id}, sort=[("created_at", DESCENDING)]
    )
    if not latest_plan:
        return 0
    return sum(1 for row in latest_plan["grid"] for cell in row if cell is not None)


@rooms_bp.get("")
def list_rooms():
    db = get_db()
    rooms = list(db.rooms.find())
    out = []
    for room in rooms:
        doc = dict(room)
        doc["filled_seats"] = _filled_seats(db, room["_id"])
        out.append(doc)
    return jsonify(serialize_many(out))


@rooms_bp.post("")
def create_room():
    db = get_db()
    data = request.get_json(silent=True) or {}
    room_no = data.get("room_no")
    rows = data.get("rows")
    cols = data.get("cols")

    if not room_no or not isinstance(rows, int) or not isinstance(cols, int):
        return error("room_no, rows, and cols are required (rows/cols must be integers)")
    if rows <= 0 or cols <= 0:
        return error("rows and cols must be positive")

    doc = {"room_no": room_no, "rows": rows, "cols": cols, "capacity": rows * cols}
    try:
        result = db.rooms.insert_one(doc)
    except DuplicateKeyError:
        return error(f"Room '{room_no}' already exists", 409)

    doc["_id"] = result.inserted_id
    doc["filled_seats"] = 0
    return jsonify(serialize_many([doc])[0]), 201


@rooms_bp.put("/<room_id>")
def update_room(room_id):
    db = get_db()
    oid = to_object_id(room_id)
    if oid is None:
        return error("Invalid room id", 400)

    data = request.get_json(silent=True) or {}
    update = {}
    for field in ("room_no", "rows", "cols"):
        if field in data:
            update[field] = data[field]
    if "rows" in update or "cols" in update:
        existing = db.rooms.find_one({"_id": oid})
        if not existing:
            return error("Room not found", 404)
        rows = update.get("rows", existing["rows"])
        cols = update.get("cols", existing["cols"])
        update["capacity"] = rows * cols

    if not update:
        return error("No valid fields to update")

    try:
        result = db.rooms.update_one({"_id": oid}, {"$set": update})
    except DuplicateKeyError:
        return error(f"Room '{update.get('room_no')}' already exists", 409)

    if result.matched_count == 0:
        return error("Room not found", 404)

    room = db.rooms.find_one({"_id": oid})
    room["filled_seats"] = _filled_seats(db, oid)
    return jsonify(serialize_many([room])[0])


@rooms_bp.delete("/<room_id>")
def delete_room(room_id):
    db = get_db()
    oid = to_object_id(room_id)
    if oid is None:
        return error("Invalid room id", 400)

    result = db.rooms.delete_one({"_id": oid})
    if result.deleted_count == 0:
        return error("Room not found", 404)
    return "", 204
