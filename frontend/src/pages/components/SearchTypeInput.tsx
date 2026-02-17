import { SearchType, type SearchTypeValue } from "../searchTypes";

interface SearchTypeInputProps {
  value: SearchTypeValue;
  onChange: (value: SearchTypeValue) => void;
}

export function SearchTypeInput({ value, onChange }: SearchTypeInputProps) {
  return (
    <div className="mb-4 flex gap-6">
      <label className="flex items-center gap-2">
        <input
          type="radio"
          name="searchType"
          value={SearchType.People}
          checked={value === SearchType.People}
          onChange={() => onChange(SearchType.People)}
          className="h-4 w-4"
        />
        <span className="font-bold">People</span>
      </label>
      <label className="flex items-center gap-2">
        <input
          type="radio"
          name="searchType"
          value={SearchType.Movies}
          checked={value === SearchType.Movies}
          onChange={() => onChange(SearchType.Movies)}
          className="h-4 w-4"
        />
        <span className="font-bold">Movies</span>
      </label>
    </div>
  );
}
