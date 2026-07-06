import { useEffect, useState } from "react";
import { createCourse, deleteCourse, listCourses, updateCourse } from "../api";

export default function CoursesPage() {
  const [courses, setCourses] = useState(null);
  const [error, setError] = useState(null);
  const [form, setForm] = useState({ name: "", batch: "" });
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState({ name: "", batch: "" });
  const [savingEdit, setSavingEdit] = useState(false);

  const load = () => {
    setError(null);
    listCourses()
      .then(setCourses)
      .catch((e) => setError(e.message));
  };

  useEffect(load, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await createCourse(form);
      setForm({ name: "", batch: "" });
      load();
    } catch (e) {
      setError(e.message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    setError(null);
    try {
      await deleteCourse(id);
      load();
    } catch (e) {
      setError(e.message);
    }
  };

  const startEdit = (course) => {
    setError(null);
    setEditingId(course._id);
    setEditForm({ name: course.name, batch: course.batch });
  };

  const cancelEdit = () => setEditingId(null);

  const saveEdit = async (id) => {
    setError(null);
    setSavingEdit(true);
    try {
      await updateCourse(id, editForm);
      setEditingId(null);
      load();
    } catch (e) {
      setError(e.message);
    } finally {
      setSavingEdit(false);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex items-baseline justify-between">
        <div>
          <h2 className="font-display text-2xl text-ink">Courses</h2>
          <p className="text-sm text-ink/50 mt-0.5">Batches enrolled for examination</p>
        </div>
        {courses?.length > 0 && (
          <p className="font-mono text-xs text-ink/40">
            {courses.length} course{courses.length === 1 ? "" : "s"}
          </p>
        )}
      </div>

      <form onSubmit={handleSubmit} className="card p-5">
        <p className="font-mono text-[11px] uppercase tracking-wide text-ink/40 mb-4">
          Add a Course
        </p>
        <div className="flex flex-wrap items-end gap-4">
          <div>
            <label className="field-label">Course Name</label>
            <input
              required
              className="field-input"
              placeholder="Computer Science"
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
            />
          </div>
          <div>
            <label className="field-label">Batch</label>
            <input
              required
              className="field-input"
              placeholder="2026"
              value={form.batch}
              onChange={(e) => setForm({ ...form, batch: e.target.value })}
            />
          </div>
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? "Adding…" : "Add Course"}
          </button>
        </div>
      </form>

      {error && <p className="text-alert text-sm">{error}</p>}

      <div className="card overflow-hidden">
        {courses === null ? (
          <p className="empty-state">Loading courses…</p>
        ) : courses.length === 0 ? (
          <p className="empty-state">No courses yet — add one above to get started.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Batch</th>
                <th>Students</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {courses.map((course) => {
                const isEditing = editingId === course._id;

                if (isEditing) {
                  return (
                    <tr key={course._id}>
                      <td>
                        <input
                          className="field-input py-1"
                          value={editForm.name}
                          onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                        />
                      </td>
                      <td>
                        <input
                          className="field-input py-1 w-24"
                          value={editForm.batch}
                          onChange={(e) => setEditForm({ ...editForm, batch: e.target.value })}
                        />
                      </td>
                      <td className="font-mono text-xs text-ink/50">{course.student_count}</td>
                      <td className="text-right whitespace-nowrap">
                        <button
                          onClick={() => saveEdit(course._id)}
                          disabled={savingEdit}
                          className="link-brass mr-3"
                        >
                          {savingEdit ? "Saving…" : "Save"}
                        </button>
                        <button onClick={cancelEdit} className="text-xs text-ink/40 hover:text-ink">
                          Cancel
                        </button>
                      </td>
                    </tr>
                  );
                }

                return (
                  <tr key={course._id}>
                    <td className="font-medium">{course.name}</td>
                    <td className="font-mono text-xs text-ink/70">{course.batch}</td>
                    <td className="font-mono text-xs text-ink/70">{course.student_count}</td>
                    <td className="text-right whitespace-nowrap">
                      <button onClick={() => startEdit(course)} className="link-brass mr-3">
                        Edit
                      </button>
                      <button onClick={() => handleDelete(course._id)} className="link-danger">
                        Delete
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}
