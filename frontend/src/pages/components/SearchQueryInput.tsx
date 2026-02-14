interface SearchQueryInputProps {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}

export function SearchQueryInput({
  value,
  onChange,
  placeholder = "e.g. Chewbacca, Yoda, Boba Fett",
}: SearchQueryInputProps) {
  return (
    <div className="mb-4">
      <input
        type="text"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        className="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
        aria-label="Search"
      />
    </div>
  );
}
