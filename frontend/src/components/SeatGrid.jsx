const COURSE_COLORS = [
  "var(--color-course-1)",
  "var(--color-course-2)",
  "var(--color-course-3)",
  "var(--color-course-4)",
  "var(--color-course-5)",
  "var(--color-course-6)",
];

function rowLabel(index) {
  let label = "";
  let n = index;
  do {
    label = String.fromCharCode(65 + (n % 26)) + label;
    n = Math.floor(n / 26) - 1;
  } while (n >= 0);
  return label;
}

function buildCourseColorMap(grid) {
  const seen = new Map();
  for (const row of grid) {
    for (const cell of row) {
      if (cell && !seen.has(cell.course_id)) {
        seen.set(cell.course_id, {
          name: cell.course_name,
          color: COURSE_COLORS[seen.size % COURSE_COLORS.length],
        });
      }
    }
  }
  return seen;
}

function hasSameCourseNeighbor(grid, r, c) {
  const student = grid[r][c];
  if (!student) return false;
  for (let dr = -1; dr <= 1; dr++) {
    for (let dc = -1; dc <= 1; dc++) {
      if (dr === 0 && dc === 0) continue;
      const nr = r + dr;
      const nc = c + dc;
      if (nr < 0 || nr >= grid.length || nc < 0 || nc >= grid[0].length) continue;
      const neighbor = grid[nr][nc];
      if (neighbor && neighbor.course_id === student.course_id) return true;
    }
  }
  return false;
}

export default function SeatGrid({ plan }) {
  const { grid, room_no, rows, cols } = plan;
  const courseColors = buildCourseColorMap(grid);
  const seatedCount = grid.reduce(
    (sum, row) => sum + row.filter((cell) => cell !== null).length,
    0
  );

  const conflictFlags = grid.map((row, r) =>
    row.map((_, c) => hasSameCourseNeighbor(grid, r, c))
  );

  return (
    <div className="seat-grid-print card overflow-hidden">
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-rule px-5 py-4">
        <div>
          <h3 className="font-display text-xl text-ink">Room {room_no}</h3>
          <p className="font-mono text-xs text-ink/50 mt-0.5">
            {rows} × {cols} · {seatedCount} seated
          </p>
        </div>
        <div className="flex items-center gap-3 print:hidden">
          <span
            className={`badge ${plan.conflicts > 0 ? "bg-alert/10 text-alert" : "bg-ok/10 text-ok"}`}
          >
            {plan.conflicts > 0
              ? `${plan.conflicts} unavoidable adjacenc${plan.conflicts === 1 ? "y" : "ies"} flagged`
              : "no adjacent same-course pairs"}
          </span>
          <button onClick={() => window.print()} className="btn-primary !py-1.5 !px-3 text-xs">
            Print
          </button>
        </div>
        <span
          className={`hidden print:inline-flex badge ${
            plan.conflicts > 0 ? "bg-alert/10 text-alert" : "bg-ok/10 text-ok"
          }`}
        >
          {plan.conflicts > 0
            ? `${plan.conflicts} unavoidable adjacenc${plan.conflicts === 1 ? "y" : "ies"} flagged`
            : "no adjacent same-course pairs"}
        </span>
      </div>

      <div className="overflow-x-auto p-5">
        <table className="border-separate" style={{ borderSpacing: "3px" }}>
          <thead>
            <tr>
              <th className="w-8"></th>
              {Array.from({ length: cols }, (_, c) => (
                <th key={c} className="font-mono text-[11px] text-ink/40 font-normal pb-1.5">
                  {c + 1}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {grid.map((row, r) => (
              <tr key={r}>
                <td className="font-mono text-[11px] text-ink/40 pr-2.5 text-right">
                  {rowLabel(r)}
                </td>
                {row.map((cell, c) => {
                  const color = cell ? courseColors.get(cell.course_id)?.color : null;
                  return (
                    <td key={c}>
                      <div
                        className={`relative w-16 h-10 border rounded-md flex items-center justify-center transition-shadow ${
                          cell
                            ? "bg-white border-rule hover:shadow-sm"
                            : "bg-rule/10 border-rule/50 border-dashed"
                        }`}
                        style={cell ? { borderLeftWidth: "3px", borderLeftColor: color } : undefined}
                        title={cell ? `${cell.name} (${cell.course_name})` : "empty"}
                      >
                        {cell && (
                          <span className="font-mono text-[11px] text-ink truncate px-1">
                            {cell.exam_roll}
                          </span>
                        )}
                        {cell && conflictFlags[r][c] && (
                          <span className="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-alert" />
                        )}
                      </div>
                    </td>
                  );
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="border-t border-rule px-5 py-4 flex flex-wrap items-center gap-4">
        {[...courseColors.entries()].map(([courseId, { name, color }]) => (
          <div key={courseId} className="flex items-center gap-1.5 text-xs text-ink/70">
            <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: color }} />
            {name}
          </div>
        ))}
        {plan.conflicts > 0 && (
          <p className="text-xs text-ink/45 italic ml-auto">
            {plan.conflicts} adjacency conflict{plan.conflicts === 1 ? "" : "s"} — mathematically
            unavoidable with fewer than 4 courses filling a room, not an error.
          </p>
        )}
      </div>
    </div>
  );
}
