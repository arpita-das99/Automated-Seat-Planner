import { useEffect, useState } from "react";
import { listSeatPlans } from "../api";
import SeatGrid from "../components/SeatGrid";

export default function HistoryPage() {
  const [plans, setPlans] = useState(null);
  const [selectedId, setSelectedId] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    listSeatPlans()
      .then(setPlans)
      .catch((e) => setError(e.message));
  }, []);

  const selected = plans?.find((p) => p._id === selectedId) || null;

  return (
    <div className="space-y-8">
      <div className="flex items-baseline justify-between">
        <div>
          <h2 className="font-display text-2xl text-ink">History</h2>
          <p className="text-sm text-ink/50 mt-0.5">Previously generated seating plans</p>
        </div>
        {plans?.length > 0 && (
          <p className="font-mono text-xs text-ink/40">
            {plans.length} plan{plans.length === 1 ? "" : "s"}
          </p>
        )}
      </div>

      {error && <p className="text-alert text-sm">{error}</p>}

      <div className="card overflow-hidden">
        {plans === null ? (
          <p className="empty-state">Loading history…</p>
        ) : plans.length === 0 ? (
          <p className="empty-state">No seat plans generated yet.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Room</th>
                <th>Generated</th>
                <th>Conflicts</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {plans.map((p) => (
                <tr key={p._id} className={p._id === selectedId ? "bg-paper/70" : ""}>
                  <td className="font-medium">{p.room_no}</td>
                  <td className="font-mono text-xs text-ink/70">
                    {new Date(p.created_at).toLocaleString()}
                  </td>
                  <td>
                    <span
                      className={`badge ${
                        p.conflicts > 0 ? "bg-alert/10 text-alert" : "bg-ok/10 text-ok"
                      }`}
                    >
                      {p.conflicts}
                    </span>
                  </td>
                  <td className="text-right">
                    <button onClick={() => setSelectedId(p._id)} className="link-brass">
                      View
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {selected && <SeatGrid plan={selected} />}
    </div>
  );
}
