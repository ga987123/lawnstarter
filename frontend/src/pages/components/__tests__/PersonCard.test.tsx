import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "../../../__tests__/utils";
import { PersonCard } from "../PersonCard";
import type { Person } from "../../../shared/api/types";

describe("PersonCard", () => {
  const basePerson: Person = {
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
  };

  it("renders person name and details", () => {
    render(<PersonCard person={basePerson} />);
    expect(screen.getByRole("heading", { level: 1 })).toHaveTextContent(
      "Luke Skywalker",
    );
    expect(screen.getByText(/Birth Year: 19BBY/i)).toBeInTheDocument();
    expect(screen.getByText(/Gender: male/i)).toBeInTheDocument();
    expect(screen.getByText(/Eye Color: blue/i)).toBeInTheDocument();
    expect(screen.getByText(/Hair Color: blond/i)).toBeInTheDocument();
    expect(screen.getByText(/Height: 172/i)).toBeInTheDocument();
    expect(screen.getByText(/Mass: 77/i)).toBeInTheDocument();
  });

  it("shows film links when films present", () => {
    const person: Person = {
      ...basePerson,
      films: [{ id: 1, name: "A New Hope" }],
    };
    render(<PersonCard person={person} />);
    expect(screen.getByText("Movies")).toBeInTheDocument();
    const link = screen.getByRole("link", { name: "A New Hope" });
    expect(link).toHaveAttribute("href", "/film/1");
  });

  it("shows No movies when films is empty", () => {
    render(<PersonCard person={basePerson} />);
    expect(screen.getByText("No movies")).toBeInTheDocument();
  });
});
