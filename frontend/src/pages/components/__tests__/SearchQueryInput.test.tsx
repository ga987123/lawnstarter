import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import { render } from "@testing-library/react";
import { SearchQueryInput } from "../SearchQueryInput";

describe("SearchQueryInput", () => {
  it("renders input with value and placeholder", () => {
    const onChange = vi.fn();
    render(
      <SearchQueryInput value="luke" onChange={onChange} placeholder="Search…" />,
    );
    const input = screen.getByRole("textbox", { name: /search/i });
    expect(input).toHaveValue("luke");
    expect(input).toHaveAttribute("placeholder", "Search…");
  });

  it("uses default placeholder when not provided", () => {
    render(<SearchQueryInput value="" onChange={vi.fn()} />);
    expect(
      screen.getByPlaceholderText(/e.g. Chewbacca, Yoda/i),
    ).toBeInTheDocument();
  });

  it("calls onChange with new value when user types", async () => {
    const user = userEvent.setup();
    const onChange = vi.fn();
    render(<SearchQueryInput value="" onChange={onChange} />);
    const input = screen.getByRole("textbox");
    await user.type(input, "yoda");
    expect(onChange).toHaveBeenCalled();
  });
});
