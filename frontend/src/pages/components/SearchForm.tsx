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
    <aside className="min-w-[250px] bg-white p-6 rounded-sm shadow-md h-55 border border-[.5px]">
      <form onSubmit={onSubmit}>
        <h2 className="mb-6 text-md font-semibold">
          What are you searching for?
        </h2>
        <SearchTypeInput value={searchType} onChange={onSearchTypeChange} />
        <SearchQueryInput value={query} onChange={onQueryChange} />
        <Button
          type="submit"
          loading={isLoading}
          loadingText="SEARCHING..."
          className="w-full bg-[var(--color-brand)] hover:bg-[var(--color-brand-hover)]"
        >
          SEARCH
        </Button>
      </form>
    </aside>
  );
}
