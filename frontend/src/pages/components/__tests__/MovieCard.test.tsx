import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "../../../__tests__/utils";
import { MovieCard } from "../MovieCard";
import type { Film } from "../../../shared/api/types";

describe("MovieCard", () => {
  it("renders film title", () => {
    const film: Film = {
      id: 1,
      title: "A New Hope",
      episode_id: 4,
      director: "George Lucas",
      producer: "Gary Kurtz",
      release_date: "1977-05-25",
      opening_crawl: "",
      characters: [],
    };
    render(<MovieCard film={film} />);
    expect(screen.getByRole("heading", { level: 1 })).toHaveTextContent(
      "A New Hope",
    );
  });

  it("shows Opening Crawl section when opening_crawl is present", () => {
    const film: Film = {
      id: 1,
      title: "A New Hope",
      episode_id: 4,
      director: "",
      producer: "",
      release_date: "",
      opening_crawl: "It is a period of civil war.",
      characters: [],
    };
    render(<MovieCard film={film} />);
    expect(screen.getByText("Opening Crawl")).toBeInTheDocument();
    expect(screen.getByText(/It is a period of civil war/i)).toBeInTheDocument();
  });

  it("shows Characters section with links when characters present", () => {
    const film: Film = {
      id: 1,
      title: "A New Hope",
      episode_id: 4,
      director: "",
      producer: "",
      release_date: "",
      opening_crawl: "",
      characters: [{ id: 1, name: "Luke Skywalker" }],
    };
    render(<MovieCard film={film} />);
    expect(screen.getByText("Characters")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Luke Skywalker" })).toHaveAttribute(
      "href",
      "/person/1",
    );
  });

  it("does not show Characters section when characters is empty", () => {
    const film: Film = {
      id: 1,
      title: "A New Hope",
      episode_id: 4,
      director: "",
      producer: "",
      release_date: "",
      opening_crawl: "",
      characters: [],
    };
    render(<MovieCard film={film} />);
    expect(screen.queryByText("Characters")).not.toBeInTheDocument();
  });
});
