import { Link } from "react-router-dom";
import type { Film, RelatedResource } from "../../shared/api/types";

interface MovieCardProps {
  film: Film;
}

function RelatedLinks({
  items,
  basePath,
}: {
  items: RelatedResource[];
  basePath?: string;
}) {
  return (
    <span className="text-sm leading-relaxed">
      {items.map((item, i) => (
        <span key={item.id}>
          {basePath ? (
            <Link
              to={`${basePath}/${item.id}`}
              className="text-emerald-600 underline hover:text-emerald-800"
            >
              {item.name}
            </Link>
          ) : (
            <span className="text-slate-700">{item.name}</span>
          )}
          {i < items.length - 1 && ", "}
        </span>
      ))}
    </span>
  );
}

export function MovieCard({ film }: MovieCardProps) {
  return (
    <div className="rounded-xl border border-slate-200 bg-white p-6">
      <h2 className="mb-4 text-xl font-bold text-slate-900">{film.title}</h2>

      <div className="grid gap-6 md:grid-cols-2">
        {/* Left column: Opening Crawl */}
        {film.opening_crawl && (
          <div>
            <h3 className="mb-2 text-sm font-semibold text-slate-900">
              Opening Crawl
            </h3>
            <p className="whitespace-pre-line rounded-lg border border-slate-200 p-4 text-sm leading-relaxed text-slate-700">
              {film.opening_crawl}
            </p>
          </div>
        )}

        {/* Right column: Characters */}
        {film.characters.length > 0 && (
          <div>
            <h3 className="mb-2 text-sm font-semibold text-slate-900">
              Characters
            </h3>
            <RelatedLinks items={film.characters} basePath="/person" />
          </div>
        )}
      </div>
    </div>
  );
}
