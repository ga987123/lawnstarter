import { useQuery } from "@tanstack/react-query";
import { apiClient } from "../../../shared/api/client";
import type { PersonResponse } from "../types";

export function usePersonQuery(id: number, enabled = false) {
  return useQuery({
    queryKey: ["swapi", "person", id],
    queryFn: () => apiClient.get<PersonResponse>(`/swapi/people/${id}`),
    enabled,
  });
}
