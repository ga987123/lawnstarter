import { useState } from "react";
import type { SearchItem } from "../shared/api/types";
import { SearchType, type SearchTypeValue } from "./searchTypes";
import { useSearchPeople, useSearchFilms } from "../services/swapi/api/queries";
import { SearchForm } from "./components/SearchForm";
import { SearchResults } from "./components/SearchResults";

export { SearchType, type SearchTypeValue } from "./searchTypes";

export function SearchPage() {
  const [searchType, setSearchType] = useState<SearchTypeValue>(SearchType.People);
  const [query, setQuery] = useState("");
  const [submitted, setSubmitted] = useState<{ type: SearchTypeValue; query: string }>({
    type: SearchType.People,
    query: "",
  });

  const isPeople = submitted.type === SearchType.People;
  const isMovies = submitted.type === SearchType.Movies;

  const searchPeople = useSearchPeople(
    isPeople ? submitted.query : "",
    isPeople
  );
  const searchFilms = useSearchFilms(
    isMovies ? submitted.query : "",
    isMovies
  );

  const handleSearchTypeChange = (type: SearchTypeValue) => {
    setSearchType(type);
    setSubmitted({ type, query: query.trim() });
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted({ type: searchType, query: query.trim() });
  };

  const isLoading = isPeople
    ? searchPeople.isFetching
    : isMovies
      ? searchFilms.isFetching
      : false;

  const isError = isPeople
    ? searchPeople.isError
    : isMovies
      ? searchFilms.isError
      : false;

  const error = isPeople
    ? searchPeople.error
    : isMovies
      ? searchFilms.error
      : null;

  const items: SearchItem[] = isPeople
    ? searchPeople.data?.pages.flatMap((p) => p.data) ?? []
    : isMovies
      ? searchFilms.data?.data ?? []
      : [];

  const hasNextPage = isPeople ? (searchPeople.hasNextPage ?? false) : false;
  const isFetchingNextPage = isPeople ? (searchPeople.isFetchingNextPage ?? false) : false;
  const fetchNextPage = isPeople ? searchPeople.fetchNextPage : undefined;

  return (
    <div className="grid gap-8 lg:grid-cols-[1fr_2fr]">
      <SearchForm
        searchType={searchType}
        onSearchTypeChange={handleSearchTypeChange}
        query={query}
        onQueryChange={setQuery}
        onSubmit={handleSearch}
        isLoading={isLoading}
      />
      <SearchResults
        hasSearched
        isLoading={isLoading}
        isError={isError}
        error={error}
        searchType={submitted.type}
        items={items}
        hasNextPage={hasNextPage}
        isFetchingNextPage={isFetchingNextPage}
        onLoadMore={fetchNextPage}
      />
    </div>
  );
}
