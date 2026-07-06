import { useEffect, useState } from "react";
import { generateSeatPlan, listCourses, listRooms } from "../api";
import SeatGrid from "../components/SeatGrid";

export default function GeneratePage() {
  const [rooms, setRooms] = useState([]);
  const [courses, setCourses] = useState([]);
  const [roomId, setRoomId] = useState("");
  const [courseIds, setCourseIds] = useState([]);
  const [numStudents, setNumStudents] = useState("");
  const [plan, setPlan] = useState(null);
  const [error, setError] = useState(null);
  const [generating, setGenerating] = useState(false);

  useEffect(() => {
    listRooms().then(setRooms).catch((e) => setError(e.message));
    listCourses().then(setCourses).catch((e) => setError(e.message));
  }, []);

  const toggleCourse = (id) => {
    setCourseIds((prev) => (prev.includes(id) ? prev.filter((c) => c !== id) : [...prev, id]));
  };

  const handleGenerate = async () => {
    setError(null);
    if (!roomId || courseIds.length === 0) {
      setError("Select a room and at least one course.");
      return;
    }
    setGenerating(true);
    try {
      const result = await generateSeatPlan({
        room_id: roomId,
        course_ids: courseIds,
        ...(numStudents ? { num_students: Number(numStudents) } : {}),
      });
      setPlan(result);
    } catch (e) {
      setError(e.message);
    } finally {
      setGenerating(false);
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h2 className="font-display text-2xl text-ink">Generate a Seating Plan</h2>
        <p className="text-sm text-ink/50 mt-0.5">
          Assign students to seats, roll-ordered per batch, with adjacency conflicts minimized
        </p>
      </div>

      <div className="card p-5 space-y-5">
        <div>
          <label className="field-label">Room</label>
          <select
            className="field-input"
            value={roomId}
            onChange={(e) => setRoomId(e.target.value)}
          >
            <option value="" disabled>
              Select a room
            </option>
            {rooms.map((r) => (
              <option key={r._id} value={r._id}>
                {r.room_no} ({r.rows}×{r.cols} · {r.capacity} seats)
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="field-label">Courses</label>
          {courses.length === 0 ? (
            <p className="text-sm text-ink/40">No courses yet — add one on the Courses tab.</p>
          ) : (
            <div className="flex flex-wrap gap-2">
              {courses.map((c) => {
                const active = courseIds.includes(c._id);
                return (
                  <button
                    type="button"
                    key={c._id}
                    onClick={() => toggleCourse(c._id)}
                    className={`text-sm px-3 py-1.5 rounded-full border transition-colors ${
                      active
                        ? "bg-brass text-white border-brass"
                        : "border-rule text-ink/70 hover:border-brass/50"
                    }`}
                  >
                    {c.name} <span className="opacity-70">({c.batch})</span>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        <div>
          <label className="field-label">Student count cap (optional)</label>
          <input
            type="number"
            min="1"
            className="field-input w-32"
            placeholder="all"
            value={numStudents}
            onChange={(e) => setNumStudents(e.target.value)}
          />
        </div>

        <button onClick={handleGenerate} disabled={generating} className="btn-primary">
          {generating ? "Generating…" : "Generate"}
        </button>
      </div>

      {error && <p className="text-alert text-sm">{error}</p>}

      {plan && <SeatGrid plan={plan} />}
    </div>
  );
}
