import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "@testing-library/react";
import { Button } from "../Button";

describe("Button", () => {
  it("renders children when not loading", () => {
    render(<Button>Click me</Button>);
    expect(screen.getByRole("button", { name: /click me/i })).toBeInTheDocument();
  });

  it("shows loadingText and is disabled when loading", () => {
    render(<Button loading>Submit</Button>);
    const btn = screen.getByRole("button");
    expect(btn).toHaveTextContent("Loading...");
    expect(btn).toBeDisabled();
  });

  it("shows custom loadingText when provided", () => {
    render(<Button loading loadingText="SEARCHING...">Search</Button>);
    expect(screen.getByRole("button")).toHaveTextContent("SEARCHING...");
  });

  it("is disabled when disabled prop is true", () => {
    render(<Button disabled>Submit</Button>);
    expect(screen.getByRole("button")).toBeDisabled();
  });

  it("spreads extra props", () => {
    render(<Button type="submit" aria-label="Send form">Send</Button>);
    const btn = screen.getByRole("button", { name: /send form/i });
    expect(btn).toHaveAttribute("type", "submit");
  });
});
