import { useQuery, useInfiniteQuery } from "@tanstack/react-query";
import { apiClient } from "../../../shared/api/client";
import type {
  PersonResponse,
  PersonListResponse,
  FilmResponse,
  FilmListResponse,
} from "../../../shared/api/types";

const DEFAULT_LIMIT = 10;

export function usePersonQuery(id: number, enabled = false) {
  return useQuery({
    queryKey: ["swapi", "person", id],
    queryFn: () => apiClient.get<PersonResponse>(`/swapi/people/${id}`),
    enabled,
  });
}

export function useFilmQuery(id: number, enabled = false) {
  return useQuery({
    queryKey: ["swapi", "film", id],
    queryFn: () => apiClient.get<FilmResponse>(`/swapi/films/${id}`),
    enabled,
  });
}

export function useSearchPeople(query: string, enabled: boolean) {
  return useInfiniteQuery({
    queryKey: ["swapi", "search", "people", query],
    queryFn: ({ pageParam }) =>
      apiClient.get<PersonListResponse>(
        `/swapi/people?name=${encodeURIComponent(query)}&page=${pageParam}&limit=${DEFAULT_LIMIT}`
      ),
    initialPageParam: 1,
    getNextPageParam: (lastPage) =>
      lastPage.meta.has_next_page
        ? lastPage.meta.current_page + 1
        : undefined,
    enabled,
  });
}

export function useSearchFilms(query: string, enabled: boolean) {
  return useQuery({
    queryKey: ["swapi", "search", "films", query],
    queryFn: () =>
      apiClient.get<FilmListResponse>(
        `/swapi/films?name=${encodeURIComponent(query)}`
      ),
    enabled,
  });
}
