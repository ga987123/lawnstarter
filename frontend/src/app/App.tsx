import { BrowserRouter, Route, Routes } from "react-router-dom";
import { FilmDetailPage } from "../pages/FilmDetailPage";
import { PersonDetailPage } from "../pages/PersonDetailPage";
import { SearchPage } from "../pages/SearchPage";

export function App() {
  return (
    <BrowserRouter>
      <div className="mx-auto max-w-5xl p-8 font-sans">
        <Routes>
          <Route path="/" element={<SearchPage />} />
          <Route path="/person/:id" element={<PersonDetailPage />} />
          <Route path="/film/:id" element={<FilmDetailPage />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}
