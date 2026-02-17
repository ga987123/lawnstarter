import { screen } from "@testing-library/react";
import { Route, Routes } from "react-router-dom";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { render } from "../../__tests__/utils";
import { FilmDetailPage } from "../FilmDetailPage";
import { useFilmQuery } from "../../services/swapi/api/queries";

vi.mock("../../services/swapi/api/queries", () => ({
  usePersonQuery: vi.fn(),
  useFilmQuery: vi.fn(),
  useSearchPeople: vi.fn(),
  useSearchFilms: vi.fn(),
}));

function FilmDetailPageWithRoute() {
  return (
    <Routes>
      <Route path="/film/:id" element={<FilmDetailPage />} />
    </Routes>
  );
}

describe("FilmDetailPage", () => {
  beforeEach(() => {
    vi.mocked(useFilmQuery).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: false,
      error: null,
    } as never);
  });

  it("shows Invalid film ID and back link for invalid id", () => {
    render(<FilmDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/film/xyz"] },
    });
    expect(screen.getByText(/Invalid film ID/i)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /back to search/i })).toBeInTheDocument();
  });

  it("shows BrandLoader when loading", () => {
    vi.mocked(useFilmQuery).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    } as never);
    render(<FilmDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/film/1"] },
    });
    expect(screen.getByText(/Loading film/i)).toBeInTheDocument();
  });

  it("shows error message when query errors", () => {
    vi.mocked(useFilmQuery).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Not found"),
    } as never);
    render(<FilmDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/film/999"] },
    });
    expect(screen.getByText(/Error:/i)).toHaveTextContent("Not found");
  });

  it("shows MovieCard and back link on success", () => {
    vi.mocked(useFilmQuery).mockReturnValue({
      data: {
        data: {
          id: 1,
          title: "A New Hope",
          episode_id: 4,
          director: "George Lucas",
          producer: "Gary Kurtz",
          release_date: "1977-05-25",
          opening_crawl: "It is a period of civil war.",
          characters: [],
        },
      },
      isLoading: false,
      isError: false,
      error: null,
    } as never);
    render(<FilmDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/film/1"] },
    });
    expect(screen.getByText("A New Hope")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /back to search/i })).toBeInTheDocument();
  });
});
