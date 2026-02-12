import type { Person } from "../../services/swapi/types";

interface PersonCardProps {
  person: Person;
}

export function PersonCard({ person }: PersonCardProps) {
  return (
    <div className="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-6">
      <h2 className="mb-4 text-xl font-bold text-slate-900">{person.name}</h2>
      <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
        <dt className="font-medium text-slate-500">Height</dt>
        <dd className="text-slate-800">{person.height} cm</dd>
        <dt className="font-medium text-slate-500">Mass</dt>
        <dd className="text-slate-800">{person.mass} kg</dd>
        <dt className="font-medium text-slate-500">Birth Year</dt>
        <dd className="text-slate-800">{person.birth_year}</dd>
        <dt className="font-medium text-slate-500">Gender</dt>
        <dd className="text-slate-800">{person.gender}</dd>
      </dl>
    </div>
  );
}
