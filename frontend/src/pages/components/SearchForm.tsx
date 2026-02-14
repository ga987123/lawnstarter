import type { SearchTypeValue } from "../searchTypes";
import { Button } from "../../shared/ui/Button";
import { SearchTypeInput } from "./SearchTypeInput";
import { SearchQueryInput } from "./SearchQueryInput";

interface SearchFormProps {
  searchType: SearchTypeValue;
  onSearchTypeChange: (value: SearchTypeValue) => void;
  query: string;
  onQueryChange: (value: string) => void;
  onSubmit: (e: React.FormEvent) => void;
  isLoading?: boolean;
}

export function SearchForm({
  searchType,
  onSearchTypeChange,
  query,
  onQueryChange,
  onSubmit,
  isLoading = false,
}: SearchFormProps) {
  return (
    <aside className="lg:max-w-sm">
      <h1 className="mb-6 text-2xl font-bold text-slate-900">SWStarter</h1>
      <form onSubmit={onSubmit}>
        <h2 className="mb-4 text-lg font-medium text-slate-700">
          What are you searching for?
        </h2>
        <SearchTypeInput value={searchType} onChange={onSearchTypeChange} />
        <SearchQueryInput value={query} onChange={onQueryChange} />
        <Button
          type="submit"
          loading={isLoading}
          className="w-full bg-slate-500 hover:bg-slate-600"
        >
          SEARCH
        </Button>
      </form>
    </aside>
  );
}
