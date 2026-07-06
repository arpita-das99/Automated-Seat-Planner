import { useState } from "react";
import RoomsPage from "./pages/RoomsPage";
import CoursesPage from "./pages/CoursesPage";
import StudentsPage from "./pages/StudentsPage";
import GeneratePage from "./pages/GeneratePage";
import HistoryPage from "./pages/HistoryPage";

const TABS = [
  { id: "rooms", label: "Rooms", Component: RoomsPage },
  { id: "courses", label: "Courses", Component: CoursesPage },
  { id: "students", label: "Students", Component: StudentsPage },
  { id: "generate", label: "Generate", Component: GeneratePage },
  { id: "history", label: "History", Component: HistoryPage },
];

function App() {
  const [activeTab, setActiveTab] = useState("rooms");
  const ActivePage = TABS.find((t) => t.id === activeTab).Component;

  return (
    <div className="min-h-screen bg-paper">
      <header className="bg-white border-b border-rule shadow-[0_1px_0_var(--color-rule)]">
        <div className="max-w-6xl mx-auto px-6 pt-7 pb-5 flex items-center gap-4">
          <div className="w-11 h-11 rounded-full border-2 border-brass flex items-center justify-center shrink-0">
            <span className="font-display text-lg text-brass">§</span>
          </div>
          <div>
            <p className="font-mono text-[11px] uppercase tracking-[0.2em] text-ink/45">
              Examination Administration
            </p>
            <h1 className="font-display text-[1.75rem] leading-tight text-ink">
              Seat Allotment Register
            </h1>
          </div>
        </div>
        <nav className="max-w-6xl mx-auto px-6 flex gap-1">
          {TABS.map((tab, i) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`relative px-4 py-2.5 text-sm rounded-t-md transition-colors ${
                activeTab === tab.id
                  ? "text-ink font-medium bg-paper/70"
                  : "text-ink/45 hover:text-ink hover:bg-paper/40"
              }`}
            >
              <span className="font-mono text-[10px] text-ink/35 mr-1.5">
                {String(i + 1).padStart(2, "0")}
              </span>
              {tab.label}
              {activeTab === tab.id && (
                <span className="absolute left-0 right-0 -bottom-px h-0.5 bg-brass rounded-full" />
              )}
            </button>
          ))}
        </nav>
      </header>

      <main className="max-w-6xl mx-auto px-6 py-10">
        <ActivePage />
      </main>
    </div>
  );
}

export default App;
