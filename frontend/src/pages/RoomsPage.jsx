import { useEffect, useState } from "react";
import { createRoom, deleteRoom, listRooms, updateRoom } from "../api";

export default function RoomsPage() {
  const [rooms, setRooms] = useState(null);
  const [error, setError] = useState(null);
  const [form, setForm] = useState({ room_no: "", rows: "", cols: "" });
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState({ room_no: "", rows: "", cols: "" });
  const [savingEdit, setSavingEdit] = useState(false);

  const load = () => {
    setError(null);
    listRooms()
      .then(setRooms)
      .catch((e) => setError(e.message));
  };

  useEffect(load, []);

  const capacityPreview =
    Number(form.rows) > 0 && Number(form.cols) > 0 ? Number(form.rows) * Number(form.cols) : null;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await createRoom({
        room_no: form.room_no,
        rows: Number(form.rows),
        cols: Number(form.cols),
      });
      setForm({ room_no: "", rows: "", cols: "" });
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
      await deleteRoom(id);
      load();
    } catch (e) {
      setError(e.message);
    }
  };

  const startEdit = (room) => {
    setError(null);
    setEditingId(room._id);
    setEditForm({ room_no: room.room_no, rows: room.rows, cols: room.cols });
  };

  const cancelEdit = () => {
    setEditingId(null);
  };

  const saveEdit = async (id) => {
    setError(null);
    setSavingEdit(true);
    try {
      await updateRoom(id, {
        room_no: editForm.room_no,
        rows: Number(editForm.rows),
        cols: Number(editForm.cols),
      });
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
          <h2 className="font-display text-2xl text-ink">Rooms</h2>
          <p className="text-sm text-ink/50 mt-0.5">Examination halls available for seating</p>
        </div>
        {rooms?.length > 0 && (
          <p className="font-mono text-xs text-ink/40">
            {rooms.length} room{rooms.length === 1 ? "" : "s"}
          </p>
        )}
      </div>

      <form onSubmit={handleSubmit} className="card p-5">
        <p className="font-mono text-[11px] uppercase tracking-wide text-ink/40 mb-4">
          Add a Room
        </p>
        <div className="flex flex-wrap items-end gap-4">
          <div>
            <label className="field-label">Room No.</label>
            <input
              required
              className="field-input"
              placeholder="R-101"
              value={form.room_no}
              onChange={(e) => setForm({ ...form, room_no: e.target.value })}
            />
          </div>
          <div>
            <label className="field-label">Rows</label>
            <input
              required
              type="number"
              min="1"
              className="field-input w-20"
              value={form.rows}
              onChange={(e) => setForm({ ...form, rows: e.target.value })}
            />
          </div>
          <div>
            <label className="field-label">Columns</label>
            <input
              required
              type="number"
              min="1"
              className="field-input w-20"
              value={form.cols}
              onChange={(e) => setForm({ ...form, cols: e.target.value })}
            />
          </div>
          {capacityPreview !== null && (
            <p className="font-mono text-xs text-ink/45 pb-2.5">
              capacity <span className="text-ink font-medium">{capacityPreview}</span>
            </p>
          )}
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? "Adding…" : "Add Room"}
          </button>
        </div>
      </form>

      {error && <p className="text-alert text-sm">{error}</p>}

      <div className="card overflow-hidden">
        {rooms === null ? (
          <p className="empty-state">Loading rooms…</p>
        ) : rooms.length === 0 ? (
          <p className="empty-state">No rooms yet — add one above to get started.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Room No.</th>
                <th>Dimensions</th>
                <th>Filled / Capacity</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {rooms.map((room) => {
                const pct = room.capacity ? Math.round((room.filled_seats / room.capacity) * 100) : 0;
                const isEditing = editingId === room._id;

                if (isEditing) {
                  return (
                    <tr key={room._id}>
                      <td>
                        <input
                          className="field-input py-1 w-24"
                          value={editForm.room_no}
                          onChange={(e) => setEditForm({ ...editForm, room_no: e.target.value })}
                        />
                      </td>
                      <td>
                        <div className="flex items-center gap-1.5">
                          <input
                            type="number"
                            min="1"
                            className="field-input py-1 w-14"
                            value={editForm.rows}
                            onChange={(e) => setEditForm({ ...editForm, rows: e.target.value })}
                          />
                          <span className="text-ink/40">×</span>
                          <input
                            type="number"
                            min="1"
                            className="field-input py-1 w-14"
                            value={editForm.cols}
                            onChange={(e) => setEditForm({ ...editForm, cols: e.target.value })}
                          />
                        </div>
                      </td>
                      <td className="font-mono text-xs text-ink/50">
                        {room.filled_seats}/{Number(editForm.rows) * Number(editForm.cols)}
                      </td>
                      <td className="text-right whitespace-nowrap">
                        <button
                          onClick={() => saveEdit(room._id)}
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
                  <tr key={room._id}>
                    <td className="font-medium">{room.room_no}</td>
                    <td className="font-mono text-xs text-ink/70">
                      {room.rows} × {room.cols}
                    </td>
                    <td>
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs text-ink/70 w-14">
                          {room.filled_seats}/{room.capacity}
                        </span>
                        <div className="w-20 h-1.5 rounded-full bg-rule/50 overflow-hidden">
                          <div
                            className="h-full bg-brass rounded-full"
                            style={{ width: `${pct}%` }}
                          />
                        </div>
                      </div>
                    </td>
                    <td className="text-right whitespace-nowrap">
                      <button onClick={() => startEdit(room)} className="link-brass mr-3">
                        Edit
                      </button>
                      <button onClick={() => handleDelete(room._id)} className="link-danger">
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
