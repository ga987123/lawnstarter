import type { ReactNode } from "react";
import { render, waitFor } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import {
  usePersonQuery,
  useFilmQuery,
  useSearchPeople,
  useSearchFilms,
} from "../queries";
import { apiClient } from "../../../../shared/api/client";

vi.mock("../../../../shared/api/client", () => ({
  apiClient: { get: vi.fn() },
}));

function wrapper({ children }: { children: ReactNode }) {
  const client = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return (
    <QueryClientProvider client={client}>{children}</QueryClientProvider>
  );
}

describe("usePersonQuery", () => {
  beforeEach(() => {
    vi.mocked(apiClient.get).mockClear();
    vi.mocked(apiClient.get).mockResolvedValue({ data: {} } as never);
  });

  it("calls apiClient.get with /swapi/people/{id} when enabled", async () => {
    function Test() {
      usePersonQuery(1, true);
      return null;
    }
    render(<Test />, { wrapper });
    await waitFor(() => {
      expect(apiClient.get).toHaveBeenCalledWith("/swapi/people/1");
    });
  });

  it("does not call apiClient.get when enabled is false", () => {
    function Test() {
      usePersonQuery(1, false);
      return null;
    }
    render(<Test />, { wrapper });
    expect(apiClient.get).not.toHaveBeenCalled();
  });
});

describe("useFilmQuery", () => {
  beforeEach(() => {
    vi.mocked(apiClient.get).mockClear();
    vi.mocked(apiClient.get).mockResolvedValue({ data: {} } as never);
  });

  it("calls apiClient.get with /swapi/films/{id} when enabled", async () => {
    function Test() {
      useFilmQuery(2, true);
      return null;
    }
    render(<Test />, { wrapper });
    await waitFor(() => {
      expect(apiClient.get).toHaveBeenCalledWith("/swapi/films/2");
    });
  });
});

describe("useSearchPeople", () => {
  beforeEach(() => {
    vi.mocked(apiClient.get).mockClear();
    vi.mocked(apiClient.get).mockResolvedValue({
      data: [],
      meta: { has_next_page: false, current_page: 1, total_pages: 1, total_records: 0 },
    } as never);
  });

  it("calls apiClient.get with query and page when enabled", async () => {
    function Test() {
      useSearchPeople("luke", true);
      return null;
    }
    render(<Test />, { wrapper });
    await waitFor(() => {
      expect(apiClient.get).toHaveBeenCalledWith(
        "/swapi/people?name=luke&page=1&limit=10",
      );
    });
  });
});

describe("useSearchFilms", () => {
  beforeEach(() => {
    vi.mocked(apiClient.get).mockClear();
    vi.mocked(apiClient.get).mockResolvedValue({ data: [] } as never);
  });

  it("calls apiClient.get with name param when enabled", async () => {
    function Test() {
      useSearchFilms("hope", true);
      return null;
    }
    render(<Test />, { wrapper });
    await waitFor(() => {
      expect(apiClient.get).toHaveBeenCalledWith(
        "/swapi/films?name=hope",
      );
    });
  });
});
