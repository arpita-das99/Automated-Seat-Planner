import { useEffect, useState } from "react";
import { createStudent, deleteStudent, listCourses, listStudents } from "../api";
import StudentImport from "../components/StudentImport";

export default function StudentsPage() {
  const [students, setStudents] = useState(null);
  const [courses, setCourses] = useState([]);
  const [filterCourseId, setFilterCourseId] = useState("");
  const [error, setError] = useState(null);
  const [form, setForm] = useState({ exam_roll: "", name: "", course_id: "" });
  const [submitting, setSubmitting] = useState(false);

  const loadStudents = (courseId) => {
    setError(null);
    listStudents(courseId || undefined)
      .then(setStudents)
      .catch((e) => setError(e.message));
  };

  useEffect(() => {
    listCourses()
      .then(setCourses)
      .catch((e) => setError(e.message));
  }, []);

  useEffect(() => loadStudents(filterCourseId), [filterCourseId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await createStudent(form);
      setForm({ exam_roll: "", name: "", course_id: form.course_id });
      loadStudents(filterCourseId);
    } catch (e) {
      setError(e.message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    setError(null);
    try {
      await deleteStudent(id);
      loadStudents(filterCourseId);
    } catch (e) {
      setError(e.message);
    }
  };

  const courseName = (id) => courses.find((c) => c._id === id)?.name || id;

  return (
    <div className="space-y-8">
      <div className="flex items-baseline justify-between">
        <div>
          <h2 className="font-display text-2xl text-ink">Students</h2>
          <p className="text-sm text-ink/50 mt-0.5">Registered candidates by roll number</p>
        </div>
        {students?.length > 0 && (
          <p className="font-mono text-xs text-ink/40">
            {students.length} student{students.length === 1 ? "" : "s"}
          </p>
        )}
      </div>

      <form onSubmit={handleSubmit} className="card p-5">
        <p className="font-mono text-[11px] uppercase tracking-wide text-ink/40 mb-4">
          Add a Student
        </p>
        <div className="flex flex-wrap items-end gap-4">
          <div>
            <label className="field-label">Exam Roll</label>
            <input
              required
              className="field-input font-mono"
              placeholder="2026CS001"
              value={form.exam_roll}
              onChange={(e) => setForm({ ...form, exam_roll: e.target.value })}
            />
          </div>
          <div>
            <label className="field-label">Name</label>
            <input
              required
              className="field-input"
              placeholder="Student name"
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
            />
          </div>
          <div>
            <label className="field-label">Course</label>
            <select
              required
              className="field-input"
              value={form.course_id}
              onChange={(e) => setForm({ ...form, course_id: e.target.value })}
            >
              <option value="" disabled>
                Select a course
              </option>
              {courses.map((c) => (
                <option key={c._id} value={c._id}>
                  {c.name} ({c.batch})
                </option>
              ))}
            </select>
          </div>
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? "Adding…" : "Add Student"}
          </button>
        </div>
      </form>

      <StudentImport courses={courses} onImported={() => loadStudents(filterCourseId)} />

      {error && <p className="text-alert text-sm">{error}</p>}

      <div className="flex items-center gap-2.5">
        <label className="field-label !mb-0">Filter</label>
        <select
          className="field-input py-1"
          value={filterCourseId}
          onChange={(e) => setFilterCourseId(e.target.value)}
        >
          <option value="">All courses</option>
          {courses.map((c) => (
            <option key={c._id} value={c._id}>
              {c.name} ({c.batch})
            </option>
          ))}
        </select>
      </div>

      <div className="card overflow-hidden">
        {students === null ? (
          <p className="empty-state">Loading students…</p>
        ) : students.length === 0 ? (
          <p className="empty-state">No students yet — add one above to get started.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Exam Roll</th>
                <th>Name</th>
                <th>Course</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {students.map((s) => (
                <tr key={s._id}>
                  <td className="font-mono text-xs text-ink/70">{s.exam_roll}</td>
                  <td className="font-medium">{s.name}</td>
                  <td className="text-xs text-ink/70">{courseName(s.course_id)}</td>
                  <td className="text-right">
                    <button onClick={() => handleDelete(s._id)} className="link-danger">
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}
