import { Link } from "react-router-dom";
import type { Person } from "../../shared/api/types";

interface PersonCardProps {
  person: Person;
}

export function PersonCard({ person }: PersonCardProps) {
  return (
    <div className="mt-6 rounded-xl border border-slate-200 bg-white p-6">
      <h2 className="mb-4 text-xl font-bold text-slate-900">{person.name}</h2>
      <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
        <dt className="font-medium text-slate-500">Height</dt>
        <dd className="text-slate-800">{person.height} cm</dd>
        <dt className="font-medium text-slate-500">Mass</dt>
        <dd className="text-slate-800">{person.mass} kg</dd>
        <dt className="font-medium text-slate-500">Birth Year</dt>
        <dd className="text-slate-800">{person.birth_year}</dd>
        <dt className="font-medium text-slate-500">Gender</dt>
        <dd className="text-slate-800 capitalize">{person.gender}</dd>
        <dt className="font-medium text-slate-500">Skin Color</dt>
        <dd className="text-slate-800 capitalize">{person.skin_color}</dd>
        <dt className="font-medium text-slate-500">Hair Color</dt>
        <dd className="text-slate-800 capitalize">{person.hair_color}</dd>
        <dt className="font-medium text-slate-500">Eye Color</dt>
        <dd className="text-slate-800 capitalize">{person.eye_color}</dd>
      </dl>

      {person.films.length > 0 && (
        <div className="mt-6">
          <h3 className="mb-2 text-sm font-semibold text-slate-900">Films</h3>
          <span className="text-sm leading-relaxed">
            {person.films.map((film, i) => (
              <span key={film.id}>
                <Link
                  to={`/film/${film.id}`}
                  className="text-emerald-600 underline hover:text-emerald-800"
                >
                  {film.name}
                </Link>
                {i < person.films.length - 1 && ", "}
              </span>
            ))}
          </span>
        </div>
      )}
    </div>
  );
}
