import type { Film } from "../../shared/api/types";
import { RelatedLinks } from "../../shared/ui/RelatedLinks";

interface MovieCardProps {
  film: Film;
}

export function MovieCard({ film }: MovieCardProps) {
  return (
    <div>
      <h1 className="text-2xl font-bold">{film.title}</h1>
      <div className="flex flex-col gap-8 pt-6 sm:flex-row sm:gap-12">
        {film.opening_crawl && (
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-lg">Opening Crawl</h3>
            <div className="border-b border-slate-200 pb-3 mb-3" aria-hidden />
            <p className="whitespace-pre-line text-md">{film.opening_crawl}</p>
          </div>
        )}
        {film.characters.length > 0 && (
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-lg">Characters</h3>
            <div className="border-b border-slate-200 pb-3 mb-3" aria-hidden />
            <RelatedLinks items={film.characters} basePath="/person" />
          </div>
        )}
      </div>
    </div>
  );
}
