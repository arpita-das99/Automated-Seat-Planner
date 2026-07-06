from flask import Blueprint, jsonify, request
from pymongo.errors import DuplicateKeyError

from extensions import get_db
from utils import error, serialize_many, to_object_id

students_bp = Blueprint("students", __name__, url_prefix="/api/students")


@students_bp.get("")
def list_students():
    db = get_db()
    query = {}
    course_id = request.args.get("course_id")
    if course_id:
        oid = to_object_id(course_id)
        if oid is None:
            return error("Invalid course_id", 400)
        query["course_id"] = oid

    students = list(db.students.find(query))
    return jsonify(serialize_many(students))


def _validate_student(db, data):
    exam_roll = data.get("exam_roll")
    name = data.get("name")
    course_id = data.get("course_id")

    if not exam_roll or not name or not course_id:
        return None, "exam_roll, name, and course_id are required"

    course_oid = to_object_id(course_id)
    if course_oid is None or not db.courses.find_one({"_id": course_oid}):
        return None, "Unknown course_id"

    return {"exam_roll": exam_roll, "name": name, "course_id": course_oid}, None


@students_bp.post("")
def create_student():
    db = get_db()
    data = request.get_json(silent=True) or {}
    doc, err = _validate_student(db, data)
    if err == "Unknown course_id":
        return error(err, 404)
    if err:
        return error(err)

    try:
        result = db.students.insert_one(doc)
    except DuplicateKeyError:
        return error(f"Student with exam_roll '{doc['exam_roll']}' already exists", 409)

    doc["_id"] = result.inserted_id
    return jsonify(serialize_many([doc])[0]), 201


@students_bp.post("/bulk")
def bulk_create_students():
    db = get_db()
    data = request.get_json(silent=True) or {}
    rows = data.get("students")
    if not isinstance(rows, list):
        return error("students must be a list")

    inserted = 0
    skipped = 0
    for row in rows:
        doc, err = _validate_student(db, row if isinstance(row, dict) else {})
        if err:
            skipped += 1
            continue
        try:
            db.students.insert_one(doc)
            inserted += 1
        except DuplicateKeyError:
            skipped += 1

    return jsonify({"inserted": inserted, "skipped": skipped})


@students_bp.delete("/<student_id>")
def delete_student(student_id):
    db = get_db()
    oid = to_object_id(student_id)
    if oid is None:
        return error("Invalid student id", 400)

    result = db.students.delete_one({"_id": oid})
    if result.deleted_count == 0:
        return error("Student not found", 404)
    return "", 204
