import { useState } from "react";
import * as XLSX from "xlsx";
import { bulkCreateStudents } from "../api";

function guessColumn(headers, keywords) {
  const lower = headers.map((h) => h.toLowerCase());
  for (const kw of keywords) {
    const idx = lower.findIndex((h) => h.includes(kw));
    if (idx !== -1) return headers[idx];
  }
  return headers[0] || "";
}

function resolveCourseId(courses, rawValue) {
  const value = String(rawValue ?? "").trim().toLowerCase();
  if (!value) return null;
  const match = courses.find(
    (c) =>
      c.name.trim().toLowerCase() === value ||
      `${c.name} (${c.batch})`.trim().toLowerCase() === value
  );
  return match ? match._id : null;
}

export default function StudentImport({ courses, onImported }) {
  const [open, setOpen] = useState(false);
  const [headers, setHeaders] = useState([]);
  const [rows, setRows] = useState([]);
  const [mapping, setMapping] = useState({ exam_roll: "", name: "", course: "" });
  const [fileName, setFileName] = useState("");
  const [error, setError] = useState(null);
  const [result, setResult] = useState(null);
  const [importing, setImporting] = useState(false);

  const reset = () => {
    setHeaders([]);
    setRows([]);
    setFileName("");
    setResult(null);
    setError(null);
  };

  const handleFile = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setError(null);
    setResult(null);
    setFileName(file.name);

    try {
      const buffer = await file.arrayBuffer();
      const workbook = XLSX.read(buffer, { type: "array" });
      const sheet = workbook.Sheets[workbook.SheetNames[0]];
      const parsed = XLSX.utils.sheet_to_json(sheet, { defval: "" });

      if (parsed.length === 0) {
        setError("The file has no data rows.");
        return;
      }

      const detectedHeaders = Object.keys(parsed[0]);
      setHeaders(detectedHeaders);
      setRows(parsed);
      setMapping({
        exam_roll: guessColumn(detectedHeaders, ["roll", "id"]),
        name: guessColumn(detectedHeaders, ["name"]),
        course: guessColumn(detectedHeaders, ["course", "batch"]),
      });
    } catch (err) {
      setError("Could not read that file — is it a valid .xlsx/.xls/.csv?");
    }
  };

  const mappedRows = rows.map((row) => ({
    exam_roll: String(row[mapping.exam_roll] ?? "").trim(),
    name: String(row[mapping.name] ?? "").trim(),
    course_id: resolveCourseId(courses, row[mapping.course]),
    _courseRaw: row[mapping.course],
  }));

  const unresolvedCount = mappedRows.filter((r) => !r.course_id || !r.exam_roll || !r.name).length;
  const readyCount = mappedRows.length - unresolvedCount;

  const handleImport = async () => {
    setImporting(true);
    setError(null);
    try {
      const ready = mappedRows
        .filter((r) => r.course_id && r.exam_roll && r.name)
        .map(({ exam_roll, name, course_id }) => ({ exam_roll, name, course_id }));

      if (ready.length === 0) {
        setError("No rows could be mapped — check the column selections below.");
        return;
      }

      const res = await bulkCreateStudents(ready);
      setResult({ ...res, unresolved: unresolvedCount });
      onImported();
    } catch (err) {
      setError(err.message);
    } finally {
      setImporting(false);
    }
  };

  if (!open) {
    return (
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="text-sm text-brass font-medium hover:underline"
      >
        Import from Excel / CSV
      </button>
    );
  }

  return (
    <div className="card p-5 space-y-4">
      <div className="flex items-center justify-between">
        <p className="font-mono text-[11px] uppercase tracking-wide text-ink/40">
          Import from Spreadsheet
        </p>
        <button
          type="button"
          onClick={() => {
            setOpen(false);
            reset();
          }}
          className="text-xs text-ink/40 hover:text-ink"
        >
          Close
        </button>
      </div>

      <div>
        <label className="field-label">Spreadsheet file (.xlsx, .xls, .csv)</label>
        <input
          type="file"
          accept=".xlsx,.xls,.csv"
          onChange={handleFile}
          className="text-sm"
        />
        {fileName && <p className="text-xs text-ink/40 mt-1">{fileName}</p>}
      </div>

      {error && <p className="text-alert text-sm">{error}</p>}

      {headers.length > 0 && (
        <>
          <div className="flex flex-wrap gap-4">
            <div>
              <label className="field-label">Exam Roll column</label>
              <select
                className="field-input"
                value={mapping.exam_roll}
                onChange={(e) => setMapping({ ...mapping, exam_roll: e.target.value })}
              >
                {headers.map((h) => (
                  <option key={h} value={h}>
                    {h}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="field-label">Name column</label>
              <select
                className="field-input"
                value={mapping.name}
                onChange={(e) => setMapping({ ...mapping, name: e.target.value })}
              >
                {headers.map((h) => (
                  <option key={h} value={h}>
                    {h}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="field-label">Course column</label>
              <select
                className="field-input"
                value={mapping.course}
                onChange={(e) => setMapping({ ...mapping, course: e.target.value })}
              >
                {headers.map((h) => (
                  <option key={h} value={h}>
                    {h}
                  </option>
                ))}
              </select>
              <p className="text-xs text-ink/40 mt-1">
                Matched against course names — must match exactly (e.g. "MCA")
              </p>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Exam Roll</th>
                  <th>Name</th>
                  <th>Course (raw)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {mappedRows.slice(0, 5).map((r, i) => (
                  <tr key={i}>
                    <td className="font-mono text-xs">{r.exam_roll || "—"}</td>
                    <td>{r.name || "—"}</td>
                    <td className="text-xs text-ink/60">{String(r._courseRaw ?? "") || "—"}</td>
                    <td>
                      {r.course_id && r.exam_roll && r.name ? (
                        <span className="badge bg-ok/10 text-ok">ready</span>
                      ) : (
                        <span className="badge bg-alert/10 text-alert">unresolved</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            {mappedRows.length > 5 && (
              <p className="text-xs text-ink/40 mt-2">
                showing first 5 of {mappedRows.length} rows
              </p>
            )}
          </div>

          <div className="flex items-center gap-4">
            <button onClick={handleImport} disabled={importing} className="btn-primary">
              {importing ? "Importing…" : `Import ${readyCount} student${readyCount === 1 ? "" : "s"}`}
            </button>
            {unresolvedCount > 0 && (
              <p className="text-xs text-alert">
                {unresolvedCount} row{unresolvedCount === 1 ? "" : "s"} will be skipped (missing
                roll/name, or course name doesn't match an existing course)
              </p>
            )}
          </div>
        </>
      )}

      {result && (
        <p className="text-sm">
          Imported <span className="text-ok font-medium">{result.inserted}</span> ·
          skipped as duplicates/invalid{" "}
          <span className="text-alert font-medium">{result.skipped}</span>
          {result.unresolved > 0 && (
            <>
              {" "}
              · skipped as unresolved{" "}
              <span className="text-alert font-medium">{result.unresolved}</span>
            </>
          )}
        </p>
      )}
    </div>
  );
}
