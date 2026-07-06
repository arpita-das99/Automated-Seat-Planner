from flask import Blueprint, jsonify, request

from extensions import get_db
from utils import error, serialize_many, to_object_id

courses_bp = Blueprint("courses", __name__, url_prefix="/api/courses")


@courses_bp.get("")
def list_courses():
    db = get_db()
    courses = list(db.courses.find())
    out = []
    for course in courses:
        doc = dict(course)
        doc["student_count"] = db.students.count_documents({"course_id": course["_id"]})
        out.append(doc)
    return jsonify(serialize_many(out))


@courses_bp.post("")
def create_course():
    db = get_db()
    data = request.get_json(silent=True) or {}
    name = data.get("name")
    batch = data.get("batch")

    if not name or not batch:
        return error("name and batch are required")

    doc = {"name": name, "batch": batch}
    result = db.courses.insert_one(doc)
    doc["_id"] = result.inserted_id
    doc["student_count"] = 0
    return jsonify(serialize_many([doc])[0]), 201


@courses_bp.put("/<course_id>")
def update_course(course_id):
    db = get_db()
    oid = to_object_id(course_id)
    if oid is None:
        return error("Invalid course id", 400)

    data = request.get_json(silent=True) or {}
    update = {k: v for k, v in data.items() if k in ("name", "batch")}
    if not update:
        return error("No valid fields to update")

    result = db.courses.update_one({"_id": oid}, {"$set": update})
    if result.matched_count == 0:
        return error("Course not found", 404)

    course = db.courses.find_one({"_id": oid})
    course["student_count"] = db.students.count_documents({"course_id": oid})
    return jsonify(serialize_many([course])[0])


@courses_bp.delete("/<course_id>")
def delete_course(course_id):
    db = get_db()
    oid = to_object_id(course_id)
    if oid is None:
        return error("Invalid course id", 400)

    if db.students.count_documents({"course_id": oid}) > 0:
        return error("Cannot delete course: students are still enrolled in it", 409)

    result = db.courses.delete_one({"_id": oid})
    if result.deleted_count == 0:
        return error("Course not found", 404)
    return "", 204
