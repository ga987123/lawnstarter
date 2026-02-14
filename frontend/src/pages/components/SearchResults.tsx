import { useCallback, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import type { SearchItem } from "../../shared/api/types";
import type { SearchTypeValue } from "../searchTypes";
import { SearchType } from "../searchTypes";

interface SearchResultsProps {
  hasSearched: boolean;
  isLoading: boolean;
  isError: boolean;
  error: unknown;
  searchType: SearchTypeValue | null;
  items: SearchItem[];
  hasNextPage: boolean;
  isFetchingNextPage: boolean;
  onLoadMore?: () => void;
}

export function SearchResults({
  hasSearched,
  isLoading,
  isError,
  error,
  searchType,
  items,
  hasNextPage,
  isFetchingNextPage,
  onLoadMore,
}: SearchResultsProps) {
  const sentinelRef = useRef<HTMLDivElement>(null);
  const scrollContainerRef = useRef<HTMLDivElement>(null);

  const handleIntersect = useCallback(
    (entries: IntersectionObserverEntry[]) => {
      const entry = entries[0];
      if (entry?.isIntersecting && hasNextPage && !isFetchingNextPage && onLoadMore) {
        onLoadMore();
      }
    },
    [hasNextPage, isFetchingNextPage, onLoadMore]
  );

  useEffect(() => {
    const sentinel = sentinelRef.current;
    if (!sentinel) return;

    const observer = new IntersectionObserver(handleIntersect, {
      root: scrollContainerRef.current,
      rootMargin: "100px",
      threshold: 0,
    });

    observer.observe(sentinel);
    return () => observer.disconnect();
  }, [handleIntersect]);

  const showEmptyState = !hasSearched || (items.length === 0 && !isLoading && !isError);
  const detailPath = searchType === SearchType.People ? "/person" : "/film";

  return (
    <section>
      <h2 className="mb-4 text-xl font-bold text-slate-900">Results</h2>
      {isError && (
        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
          Error: {error instanceof Error ? error.message : "Unknown error"}
        </div>
      )}
      {showEmptyState ? (
        <p className="text-slate-600">
          There are zero matches. Use the form to search for People or Movie.
        </p>
      ) : (
        <div
          ref={scrollContainerRef}
          className="flex max-h-[32rem] flex-col gap-2 overflow-y-auto rounded-lg border border-slate-200 p-2"
        >
          {items.map((item) => (
            <div
              key={item.id}
              className="flex items-center justify-between gap-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3"
            >
              <span className="font-medium text-slate-900">{item.name}</span>
              <Link
                to={`${detailPath}/${item.id}`}
                className="rounded bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
              >
                SEE DETAILS
              </Link>
            </div>
          ))}

          {/* Sentinel element for infinite scroll */}
          <div ref={sentinelRef} className="h-1 shrink-0" />

          {isFetchingNextPage && (
            <p className="py-2 text-center text-sm text-slate-500">
              Loading more...
            </p>
          )}
        </div>
      )}
    </section>
  );
}
