/**
 * Typed API client for the SWAPI Proxy backend.
 *
 * All requests go through the Vite dev proxy (/api -> backend),
 * so no CORS configuration is needed in development.
 */

const BASE_URL = "/api";

class ApiError extends Error {
  public readonly status: number;
  public readonly detail: string;

  constructor(status: number, detail: string) {
    super(detail);
    this.name = "ApiError";
    this.status = status;
    this.detail = detail;
  }
}

async function request<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const url = `${BASE_URL}${endpoint}`;

  const response = await fetch(url, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    ...options,
  });

  if (!response.ok) {
    const error = (await response.json().catch(() => ({
      detail: "An unexpected error occurred",
    }))) as { detail?: string };

    throw new ApiError(response.status, error.detail ?? "Request failed");
  }

  return response.json() as Promise<T>;
}

export const apiClient = {
  get: <T>(endpoint: string) => request<T>(endpoint),
};

export { ApiError };
