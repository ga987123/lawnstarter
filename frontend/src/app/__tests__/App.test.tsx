import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "../../__tests__/utils";
import { AppContent } from "../App";

describe("App", () => {
  it("renders header with SWStarter", () => {
    render(<AppContent />);
    expect(screen.getByText("SWStarter")).toBeInTheDocument();
  });

  it("renders SearchPage at /", () => {
    render(<AppContent />, { routerProps: { initialEntries: ["/"] } });
    expect(
      screen.getByText(/What are you searching for?/i),
    ).toBeInTheDocument();
  });

  it("renders PersonDetailPage at /person/1", () => {
    render(<AppContent />, { routerProps: { initialEntries: ["/person/1"] } });
    expect(screen.getByText(/Loading person/i)).toBeInTheDocument();
  });

  it("renders FilmDetailPage at /film/1", () => {
    render(<AppContent />, { routerProps: { initialEntries: ["/film/1"] } });
    expect(screen.getByText(/Loading film/i)).toBeInTheDocument();
  });
});
