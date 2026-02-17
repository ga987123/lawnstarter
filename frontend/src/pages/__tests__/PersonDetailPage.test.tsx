import { screen } from "@testing-library/react";
import { Route, Routes } from "react-router-dom";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { render } from "../../__tests__/utils";
import { PersonDetailPage } from "../PersonDetailPage";
import { usePersonQuery } from "../../services/swapi/api/queries";

vi.mock("../../services/swapi/api/queries", () => ({
  usePersonQuery: vi.fn(),
  useFilmQuery: vi.fn(),
  useSearchPeople: vi.fn(),
  useSearchFilms: vi.fn(),
}));

function PersonDetailPageWithRoute() {
  return (
    <Routes>
      <Route path="/person/:id" element={<PersonDetailPage />} />
    </Routes>
  );
}

describe("PersonDetailPage", () => {
  beforeEach(() => {
    vi.mocked(usePersonQuery).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: false,
      error: null,
    } as never);
  });

  it("shows Invalid person ID and back link for invalid id", () => {
    render(<PersonDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/person/abc"] },
    });
    expect(screen.getByText(/Invalid person ID/i)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /back to search/i })).toBeInTheDocument();
  });

  it("shows BrandLoader when loading", () => {
    vi.mocked(usePersonQuery).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    } as never);
    render(<PersonDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/person/1"] },
    });
    expect(screen.getByText(/Loading person/i)).toBeInTheDocument();
  });

  it("shows error message when query errors", () => {
    vi.mocked(usePersonQuery).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Not found"),
    } as never);
    render(<PersonDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/person/999"] },
    });
    expect(screen.getByText(/Error:/i)).toHaveTextContent("Not found");
  });

  it("shows PersonCard and back link on success", () => {
    vi.mocked(usePersonQuery).mockReturnValue({
      data: {
        data: {
          id: 1,
          name: "Luke Skywalker",
          height: "172",
          mass: "77",
          birth_year: "19BBY",
          gender: "male",
          skin_color: "fair",
          hair_color: "blond",
          eye_color: "blue",
          homeworld: "",
          films: [],
          vehicles: [],
          starships: [],
        },
      },
      isLoading: false,
      isError: false,
      error: null,
    } as never);
    render(<PersonDetailPageWithRoute />, {
      routerProps: { initialEntries: ["/person/1"] },
    });
    expect(screen.getByText("Luke Skywalker")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /back to search/i })).toBeInTheDocument();
  });
});
