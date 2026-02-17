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
      if (
        entry?.isIntersecting &&
        hasNextPage &&
        !isFetchingNextPage &&
        onLoadMore
      ) {
        onLoadMore();
      }
    },
    [hasNextPage, isFetchingNextPage, onLoadMore],
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

  const showEmptyState =
    !hasSearched || (items.length === 0 && !isLoading && !isError);
  const detailPath = searchType === SearchType.People ? "/person" : "/film";

  return (
    <section
      className="bg-white p-6 rounded-sm shadow-md flex h-[36rem] flex-col gap-2 overflow-y-auto rounded-lg border border-[.5px] p-2"
      ref={scrollContainerRef}
    >
      <h2 className="text-xl font-bold text-[#000]">Results</h2>
      <hr className="border-slate-200" />
      {isError && (
        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
          Error: {error instanceof Error ? error.message : "Unknown error"}
        </div>
      )}
      {showEmptyState ? (
        <div className="min-h-96 flex flex-col items-center justify-center text-[var(--color-pinkish-grey)] font-bold">
          <p className="text-center">There are zero matches.</p>
          <p className="text-center">
            Use the form to search for People or Movie.
          </p>
        </div>
      ) : (
        <div>
          {items.map((item) => (
            <>
              <div
                key={item.id}
                className="flex items-center justify-between gap-4 py-3"
              >
                <span className="font-bold text-lg">{item.name}</span>
                <Link
                  to={`${detailPath}/${item.id}`}
                  className="rounded-full bg-[var(--color-brand)] px-4 py-1.5 text-sm font-bold text-white hover:bg-[var(--color-brand-hover)]"
                >
                  SEE DETAILS
                </Link>
              </div>
              <hr className="border-slate-300" />
            </>
          ))}

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
