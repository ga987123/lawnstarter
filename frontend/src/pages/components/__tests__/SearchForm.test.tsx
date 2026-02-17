import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import { render } from "@testing-library/react";
import { SearchForm } from "../SearchForm";
import { SearchType } from "../../searchTypes";

describe("SearchForm", () => {
  const defaultProps = {
    searchType: SearchType.People,
    onSearchTypeChange: vi.fn(),
    query: "",
    onQueryChange: vi.fn(),
    onSubmit: vi.fn(),
  };

  it("renders heading, type input, query input, and submit button", () => {
    render(<SearchForm {...defaultProps} />);
    expect(
      screen.getByText(/What are you searching for?/i),
    ).toBeInTheDocument();
    expect(screen.getByRole("radio", { name: /people/i })).toBeInTheDocument();
    expect(screen.getByRole("textbox")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /search/i })).toBeInTheDocument();
  });

  it("calls onSubmit when form is submitted", async () => {
    const user = userEvent.setup();
    const onSubmit = vi.fn((e: React.FormEvent) => e.preventDefault());
    render(<SearchForm {...defaultProps} onSubmit={onSubmit} />);
    await user.click(screen.getByRole("button", { name: /search/i }));
    expect(onSubmit).toHaveBeenCalled();
  });

  it("shows loading state on button when isLoading is true", () => {
    render(<SearchForm {...defaultProps} isLoading />);
    expect(screen.getByRole("button")).toHaveTextContent("SEARCHING...");
    expect(screen.getByRole("button")).toBeDisabled();
  });
});
