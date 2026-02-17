import { describe, it, expect, vi, beforeEach } from "vitest";
import { apiClient, ApiError } from "../client";

const mockFetch = vi.fn();

describe("apiClient", () => {
  beforeEach(() => {
    vi.stubGlobal("fetch", mockFetch);
    mockFetch.mockReset();
  });

  it("resolves with JSON data on success", async () => {
    const data = { id: 1, name: "Luke" };
    mockFetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(data),
    } as Response);

    const result = await apiClient.get<typeof data>("/swapi/people/1");
    expect(result).toEqual(data);
    expect(mockFetch).toHaveBeenCalledWith(
      "/api/swapi/people/1",
      expect.objectContaining({
        headers: expect.objectContaining({
          Accept: "application/json",
          "Content-Type": "application/json",
        }),
      }),
    );
  });

  it("throws ApiError with status and detail on 4xx/5xx", async () => {
    mockFetch.mockResolvedValue({
      ok: false,
      status: 404,
      json: () => Promise.resolve({ detail: "Not found" }),
    } as Response);

    try {
      await apiClient.get("/swapi/people/999");
      expect.fail("should have thrown");
    } catch (e) {
      expect(e).toBeInstanceOf(ApiError);
      expect((e as ApiError).name).toBe("ApiError");
      expect((e as ApiError).status).toBe(404);
      expect((e as ApiError).detail).toBe("Not found");
    }
  });

  it("uses fallback detail when response JSON has no detail", async () => {
    mockFetch.mockResolvedValue({
      ok: false,
      status: 500,
      json: () => Promise.resolve({}),
    } as Response);

    try {
      await apiClient.get("/x");
    } catch (e) {
      expect(e).toBeInstanceOf(ApiError);
      expect((e as ApiError).detail).toBe("Request failed");
    }
  });
});

describe("ApiError", () => {
  it("has name ApiError, status and detail", () => {
    const err = new ApiError(422, "Validation failed");
    expect(err.name).toBe("ApiError");
    expect(err.status).toBe(422);
    expect(err.detail).toBe("Validation failed");
    expect(err.message).toBe("Validation failed");
  });
});
