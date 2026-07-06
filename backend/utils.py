from bson import ObjectId
from bson.errors import InvalidId
from flask import jsonify


def error(message, status=400):
    return jsonify({"error": message}), status


def to_object_id(id_str):
    try:
        return ObjectId(id_str)
    except (InvalidId, TypeError):
        return None


def serialize_doc(doc):
    """Convert a Mongo document's ObjectId/datetime fields into JSON-safe values."""
    if doc is None:
        return None
    out = dict(doc)
    out["_id"] = str(out["_id"])
    for key in ("room_id", "course_id"):
        if key in out and isinstance(out[key], ObjectId):
            out[key] = str(out[key])
    if "course_ids" in out:
        out["course_ids"] = [str(cid) for cid in out["course_ids"]]
    if "created_at" in out and hasattr(out["created_at"], "isoformat"):
        out["created_at"] = out["created_at"].isoformat()
    if "grid" in out:
        out["grid"] = [
            [_serialize_cell(cell) for cell in row] for row in out["grid"]
        ]
    return out


def _serialize_cell(cell):
    if cell is None:
        return None
    cell = dict(cell)
    if isinstance(cell.get("course_id"), ObjectId):
        cell["course_id"] = str(cell["course_id"])
    return cell


def serialize_many(docs):
    return [serialize_doc(d) for d in docs]
