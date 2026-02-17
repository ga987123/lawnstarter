import "@testing-library/jest-dom/vitest";
import { vi } from "vitest";

vi.stubGlobal(
  "IntersectionObserver",
  class MockIntersectionObserver {
    observe = vi.fn();
    disconnect = vi.fn();
    takeRecords = () => [];
    unobserve = vi.fn();
  },
);