from flask import Flask, jsonify
from flask_cors import CORS

from config import Config
from extensions import init_db
from routes.courses import courses_bp
from routes.rooms import rooms_bp
from routes.seat_plans import seat_plans_bp
from routes.students import students_bp


def create_app(config_object=Config):
    app = Flask(__name__)
    app.config.from_object(config_object)

    CORS(app, origins=[app.config["FRONTEND_ORIGIN"]])

    init_db(app)

    app.register_blueprint(rooms_bp)
    app.register_blueprint(courses_bp)
    app.register_blueprint(students_bp)
    app.register_blueprint(seat_plans_bp)

    @app.get("/api/health")
    def health():
        return jsonify({"status": "ok"})

    return app


if __name__ == "__main__":
    app = create_app()
    app.run(debug=True, port=5008)
