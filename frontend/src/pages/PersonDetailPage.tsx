import { Link, useParams } from "react-router-dom";
import { BrandLoader } from "../shared/ui/BrandLoader";
import { usePersonQuery } from "../services/swapi/api/queries";
import { PersonCard } from "./components/PersonCard";

export function PersonDetailPage() {
  const { id } = useParams<{ id: string }>();
  const personId = id != null ? Number(id) : NaN;
  const { data, isLoading, isError, error } = usePersonQuery(
    personId,
    !Number.isNaN(personId),
  );

  if (Number.isNaN(personId)) {
    return (
      <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
        Invalid person ID.{" "}
        <Link to="/" className="underline">
          Back to search
        </Link>
      </div>
    );
  }

  if (isLoading) {
    return <BrandLoader label="Loading person…" />;
  }

  if (isError) {
    return (
      <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
        Error: {error instanceof Error ? error.message : "Unknown error"}{" "}
        <Link to="/" className="underline">
          Back to search
        </Link>
      </div>
    );
  }

  const person = data?.data;
  if (!person) {
    return (
      <div className="text-slate-600">
        No person data.{" "}
        <Link to="/" className="underline">
          Back to search
        </Link>
      </div>
    );
  }

  return (
    <div className="rounded-sm bg-white p-8 shadow-md">
      <PersonCard person={person} />

      <div className="mt-10">
        <Link
          to="/"
          className="rounded-full bg-[var(--color-brand)] px-8 py-3 text-sm font-bold  text-white hover:bg-[var(--color-brand-hover)]"
        >
          BACK TO SEARCH
        </Link>
      </div>
    </div>
  );
}
