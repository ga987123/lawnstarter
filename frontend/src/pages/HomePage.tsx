import { useState } from "react";
import { Button } from "../shared/ui/Button";
import { usePersonQuery } from "../services/swapi/api/queries";
import { PersonCard } from "./components/PersonCard";

export function HomePage() {
  const [enabled, setEnabled] = useState(false);
  const { data, isLoading, isError, error } = usePersonQuery(1, enabled);

  return (
    <main>
      <h1 className="mb-1 text-3xl font-bold text-slate-900">SWAPI Proxy</h1>
      <p className="mb-8 text-lg font-semibold text-green-600">Status: OK</p>

      <Button loading={isLoading} onClick={() => setEnabled(true)}>
        Fetch Person #1
      </Button>

      {isError && (
        <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
          Error: {error instanceof Error ? error.message : "Unknown error"}
        </div>
      )}

      {data?.data && <PersonCard person={data.data} />}
    </main>
  );
}
