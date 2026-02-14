import { Link, useParams } from "react-router-dom";
import { useFilmQuery } from "../services/swapi/api/queries";
import { MovieCard } from "./components/MovieCard";

export function FilmDetailPage() {
  const { id } = useParams<{ id: string }>();
  const filmId = id != null ? Number(id) : NaN;
  const { data, isLoading, isError, error } = useFilmQuery(filmId, !Number.isNaN(filmId));

  if (Number.isNaN(filmId)) {
    return (
      <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
        Invalid film ID. <Link to="/" className="underline">Back to search</Link>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="text-slate-600">
        Loading film…
      </div>
    );
  }

  if (isError) {
    return (
      <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
        Error: {error instanceof Error ? error.message : "Unknown error"}
        {" "}
        <Link to="/" className="underline">Back to search</Link>
      </div>
    );
  }

  const film = data?.data;
  if (!film) {
    return (
      <div className="text-slate-600">
        No film data. <Link to="/" className="underline">Back to search</Link>
      </div>
    );
  }

  return (
    <div>
      <MovieCard film={film} />
      <div className="mt-6">
        <Link
          to="/"
          className="inline-block rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold uppercase tracking-wide text-white hover:bg-emerald-700"
        >
          Back to search
        </Link>
      </div>
    </div>
  );
}
