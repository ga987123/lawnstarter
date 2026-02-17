import { BrowserRouter, Route, Routes } from "react-router-dom";
import { FilmDetailPage } from "../pages/FilmDetailPage";
import { PersonDetailPage } from "../pages/PersonDetailPage";
import { SearchPage } from "../pages/SearchPage";

export function AppContent() {
  return (
    <div className="min-h-screen flex flex-col">
      <header className="bg-white py-5">
        <div className="mx-auto px-6 text-center">
          <span className="text-2xl font-bold text-[var(--color-brand)]">
            SWStarter
          </span>
        </div>
      </header>
      <main className="flex-1 mx-auto w-full max-w-5xl px-6 py-8">
        <Routes>
          <Route path="/" element={<SearchPage />} />
          <Route path="/person/:id" element={<PersonDetailPage />} />
          <Route path="/film/:id" element={<FilmDetailPage />} />
        </Routes>
      </main>
    </div>
  );
}

export function App() {
  return (
    <BrowserRouter>
      <AppContent />
    </BrowserRouter>
  );
}
