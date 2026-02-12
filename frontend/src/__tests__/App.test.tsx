import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { App } from "../app/App";
import { Providers } from "../app/providers";

describe("App", () => {
  it("renders the status OK text", () => {
    render(
      <Providers>
        <App />
      </Providers>,
    );

    expect(screen.getByText(/Status: OK/i)).toBeInTheDocument();
  });

  it("renders the fetch button", () => {
    render(
      <Providers>
        <App />
      </Providers>,
    );

    expect(
      screen.getByRole("button", { name: /Fetch Person #1/i }),
    ).toBeInTheDocument();
  });
});
