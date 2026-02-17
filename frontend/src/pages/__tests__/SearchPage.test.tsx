import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { render } from "../../__tests__/utils";
import { SearchPage } from "../SearchPage";
import { useSearchPeople, useSearchFilms } from "../../services/swapi/api/queries";

vi.mock("../../services/swapi/api/queries", () => ({
  useSearchPeople: vi.fn(),
  useSearchFilms: vi.fn(),
}));

const emptyPeople = {
  data: undefined,
  isFetching: false,
  isError: false,
  error: null,
  hasNextPage: false,
  isFetchingNextPage: false,
  fetchNextPage: vi.fn(),
};

const emptyFilms = {
  data: undefined,
  isFetching: false,
  isError: false,
  error: null,
};

describe("SearchPage", () => {
  beforeEach(() => {
    vi.mocked(useSearchPeople).mockReturnValue(emptyPeople as never);
    vi.mocked(useSearchFilms).mockReturnValue(emptyFilms as never);
  });

  it("renders SearchForm and empty SearchResults initially", () => {
    render(<SearchPage />);
    expect(
      screen.getByText(/What are you searching for?/i),
    ).toBeInTheDocument();
    expect(screen.getByText(/There are zero matches/i)).toBeInTheDocument();
  });

  it("submits form and shows results when useSearchPeople returns data", async () => {
    const user = userEvent.setup();
    vi.mocked(useSearchPeople).mockImplementation(((query: string) => {
      if (query === "luke") {
        return {
          ...emptyPeople,
          data: {
            pages: [{ data: [{ id: 1, name: "Luke Skywalker" }] }],
          },
        } as never;
      }
      return emptyPeople as never;
    }) as never);
    render(<SearchPage />);
    await user.type(screen.getByRole("textbox"), "luke");
    await user.click(screen.getByRole("button", { name: /search/i }));
    expect(screen.getByText("Luke Skywalker")).toBeInTheDocument();
  });
});
