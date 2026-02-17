import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "@testing-library/react";
import { BrandLoader } from "../BrandLoader";

describe("BrandLoader", () => {
  it("renders spinner", () => {
    const { container } = render(<BrandLoader />);
    const spinner = container.querySelector("[aria-hidden]");
    expect(spinner).toBeInTheDocument();
  });

  it("shows label when provided", () => {
    render(<BrandLoader label="Loading film…" />);
    expect(screen.getByText("Loading film…")).toBeInTheDocument();
  });

  it("does not show label when not provided", () => {
    render(<BrandLoader />);
    expect(screen.queryByText(/loading/i)).not.toBeInTheDocument();
  });
});
