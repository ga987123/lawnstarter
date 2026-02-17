import { screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import { render } from "../../../__tests__/utils";
import { SearchResults } from "../SearchResults";
import { SearchType } from "../../searchTypes";

describe("SearchResults", () => {
  const defaultProps = {
    hasSearched: false,
    isLoading: false,
    isError: false,
    error: null,
    searchType: null as "people" | "movies" | null,
    items: [],
    hasNextPage: false,
    isFetchingNextPage: false,
  };

  it("shows empty state when hasSearched is false", () => {
    render(<SearchResults {...defaultProps} />);
    expect(screen.getByText(/There are zero matches/i)).toBeInTheDocument();
    expect(screen.getByText(/Use the form to search/i)).toBeInTheDocument();
  });

  it("shows empty state when items empty and not loading", () => {
    render(<SearchResults {...defaultProps} hasSearched items={[]} />);
    expect(screen.getByText(/There are zero matches/i)).toBeInTheDocument();
  });

  it("shows error message when isError is true", () => {
    render(
      <SearchResults
        {...defaultProps}
        hasSearched
        isError
        error={new Error("Network error")}
      />,
    );
    expect(screen.getByText(/Error:/i)).toHaveTextContent("Network error");
  });

  it("renders items with name and SEE DETAILS link for people", () => {
    render(
      <SearchResults
        {...defaultProps}
        hasSearched
        searchType={SearchType.People}
        items={[{ id: 1, name: "Luke" }]}
      />,
    );
    expect(screen.getByText("Luke")).toBeInTheDocument();
    const link = screen.getByRole("link", { name: /see details/i });
    expect(link).toHaveAttribute("href", "/person/1");
  });

  it("renders items with link to /film for movies", () => {
    render(
      <SearchResults
        {...defaultProps}
        hasSearched
        searchType={SearchType.Movies}
        items={[{ id: 2, name: "A New Hope" }]}
      />,
    );
    expect(screen.getByText("A New Hope")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /see details/i })).toHaveAttribute(
      "href",
      "/film/2",
    );
  });

  it("shows Loading more when isFetchingNextPage", () => {
    render(
      <SearchResults
        {...defaultProps}
        hasSearched
        items={[{ id: 1, name: "A" }]}
        hasNextPage
        isFetchingNextPage
        onLoadMore={vi.fn()}
      />,
    );
    expect(screen.getByText(/Loading more/i)).toBeInTheDocument();
  });
});
