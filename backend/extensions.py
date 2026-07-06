from pymongo import ASCENDING, DESCENDING, MongoClient

_client = None
_db = None


def init_db(app):
    global _client, _db
    _client = MongoClient(app.config["MONGO_URI"])
    _db = _client[app.config["MONGO_DB_NAME"]]
    _create_indexes(_db)
    return _db


def set_db(db):
    """Used by tests to inject a mongomock database instead of a real MongoClient."""
    global _db
    _db = db
    _create_indexes(_db)
    return _db


def get_db():
    if _db is None:
        raise RuntimeError("Database not initialized. Call init_db(app) first.")
    return _db


def _create_indexes(db):
    db.students.create_index([("exam_roll", ASCENDING)], unique=True)
    db.rooms.create_index([("room_no", ASCENDING)], unique=True)
    db.seat_plans.create_index([("room_id", ASCENDING), ("created_at", DESCENDING)])
