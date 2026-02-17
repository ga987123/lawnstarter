import { Link } from "react-router-dom";
import type { Person } from "../../shared/api/types";

interface PersonCardProps {
  person: Person;
}

export function PersonCard({ person }: PersonCardProps) {
  return (
    <>
      <h1 className="text-2xl font-bold mb-6">{person.name}</h1>
      <div className="flex flex-col gap-8 sm:flex-row sm:gap-12 min-h-[300px]">
        {/* Details column */}
        <div className="flex-1 min-w-0">
          <h3 className="font-bold text-lg">Details</h3>
          <div className="border-b border-slate-300 pb-3 mb-3" aria-hidden />
          <ul>
            <li className="text-md">Birth Year: {person.birth_year}</li>
            <li className="text-md">Gender: {person.gender}</li>
            <li className="text-md">Eye Color: {person.eye_color}</li>
            <li className="text-md">Hair Color: {person.hair_color}</li>
            <li className="text-md">Height: {person.height}</li>
            <li className="text-md">Mass: {person.mass}</li>
          </ul>
        </div>

        {/* Movies column */}
        <div className="flex-1 min-w-0">
          <h3 className="font-bold text-lg">Movies</h3>
          <div className="border-b border-slate-200 pb-3 mb-3" aria-hidden />
          {person.films.length > 0 ? (
            <ul className="space-y-2 text-md">
              {person.films.map((film) => (
                <li key={film.id}>
                  <Link
                    to={`/film/${film.id}`}
                    className="text-blue-600 underline hover:text-blue-800"
                  >
                    {film.name}
                  </Link>
                </li>
              ))}
            </ul>
          ) : (
            <p className="text-sm text-slate-600">No movies</p>
          )}
        </div>
      </div>
    </>
  );
}
