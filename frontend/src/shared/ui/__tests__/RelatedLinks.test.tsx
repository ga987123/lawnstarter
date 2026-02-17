import { screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import { render } from "../../../__tests__/utils";
import { RelatedLinks } from "../RelatedLinks";

const items = [
  { id: 1, name: "Luke" },
  { id: 2, name: "Leia" },
];

describe("RelatedLinks", () => {
  it("renders links with basePath", () => {
    render(<RelatedLinks items={items} basePath="/person" />);
    expect(screen.getByRole("link", { name: "Luke" })).toHaveAttribute(
      "href",
      "/person/1",
    );
    expect(screen.getByRole("link", { name: "Leia" })).toHaveAttribute(
      "href",
      "/person/2",
    );
  });

  it("renders names as text when basePath is not provided", () => {
    render(<RelatedLinks items={items} />);
    expect(screen.getByText("Luke")).toBeInTheDocument();
    expect(screen.getByText("Leia")).toBeInTheDocument();
    expect(screen.queryByRole("link")).not.toBeInTheDocument();
  });

  it("renders empty span when items is empty", () => {
    const { container } = render(<RelatedLinks items={[]} />);
    expect(container.querySelector("span")).toBeInTheDocument();
  });
});
