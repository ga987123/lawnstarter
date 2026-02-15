/**
 * Shared API response types.
 * These mirror the backend response contracts.
 */

export interface RelatedResource {
  id: number;
  name: string;
}

export interface Person {
  id: number;
  name: string;
  height: string;
  mass: string;
  birth_year: string;
  gender: string;
  skin_color: string;
  hair_color: string;
  eye_color: string;
  homeworld: string;
  films: RelatedResource[];
  vehicles: string[];
  starships: string[];
}

export interface PersonResponse {
  data: Person;
}

export interface PaginationMeta {
  current_page: number;
  total_pages: number;
  total_records: number;
  has_next_page: boolean;
}

export interface SearchItem {
  id: number;
  name: string;
}

export interface PersonListResponse {
  data: SearchItem[];
  meta: PaginationMeta;
}

export interface Film {
  id: number;
  title: string;
  episode_id: number;
  director: string;
  producer: string;
  release_date: string;
  opening_crawl: string;
  characters: RelatedResource[];
}

export interface FilmResponse {
  data: Film;
}

export interface FilmListResponse {
  data: SearchItem[];
}

export interface TopQuery {
  person_id: number;
  count: number;
  percentage: number;
}

export interface TopSearchQuery {
  search_type: string;
  query: string;
  count: number;
  percentage: number;
}

export interface PopularHour {
  hour: number;
  total_count: number;
}

export interface QueryStatistics {
  top_search_queries: TopSearchQuery[];
  average_response_time_ms: number;
  popular_hours: PopularHour[];
  total_queries: number;
  computed_at: string;
}

export interface StatisticsResponse {
  data: QueryStatistics;
}

export interface HealthResponse {
  status: string;
}
