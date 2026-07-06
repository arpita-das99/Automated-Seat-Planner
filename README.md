# Seat Allotment Register

Flask + MongoDB backend, React + Vite frontend.

This project is dev-only — there is no production build/deploy step. Run
both servers locally with the commands below.

## Backend

```bash
cd backend
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
export MONGO_URI="mongodb://localhost:27017"
export FRONTEND_ORIGIN="http://localhost:5173"
python3 app.py            # http://localhost:5008
```

Requires a MongoDB instance running locally (or point `MONGO_URI` at one).

Run backend tests:

```bash
cd backend
source venv/bin/activate
python3 -m pytest -v
```

## Frontend

```bash
cd frontend
npm install
npm run dev               # http://localhost:5173
```
