import { useState } from "react";
import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import { render } from "@testing-library/react";
import { SearchTypeInput } from "../SearchTypeInput";
import { SearchType, type SearchTypeValue } from "../../searchTypes";

describe("SearchTypeInput", () => {
  it("renders two radio options (People, Movies)", () => {
    const onChange = vi.fn();
    render(<SearchTypeInput value={SearchType.People} onChange={onChange} />);
    expect(screen.getByRole("radio", { name: /people/i })).toBeInTheDocument();
    expect(screen.getByRole("radio", { name: /movies/i })).toBeInTheDocument();
  });

  it("checks People when value is People", () => {
    const onChange = vi.fn();
    render(<SearchTypeInput value={SearchType.People} onChange={onChange} />);
    expect(screen.getByRole("radio", { name: /people/i })).toBeChecked();
    expect(screen.getByRole("radio", { name: /movies/i })).not.toBeChecked();
  });

  it("checks Movies when value is Movies", () => {
    const onChange = vi.fn();
    render(<SearchTypeInput value={SearchType.Movies} onChange={onChange} />);
    expect(screen.getByRole("radio", { name: /movies/i })).toBeChecked();
    expect(screen.getByRole("radio", { name: /people/i })).not.toBeChecked();
  });

  it("calls onChange with SearchType when selection changes", async () => {
    const user = userEvent.setup();
    const onChange = vi.fn();
    function ControlledSearchTypeInput() {
      const [value, setValue] = useState<SearchTypeValue>(SearchType.People);
      return (
        <SearchTypeInput
          value={value}
          onChange={(v) => {
            setValue(v);
            onChange(v);
          }}
        />
      );
    }
    render(<ControlledSearchTypeInput />);
    await user.click(screen.getByRole("radio", { name: /movies/i }));
    expect(onChange).toHaveBeenNthCalledWith(1, SearchType.Movies);
    await user.click(screen.getByRole("radio", { name: /people/i }));
    expect(onChange).toHaveBeenNthCalledWith(2, SearchType.People);
  });
});
