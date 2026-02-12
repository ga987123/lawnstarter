/**
 * Shared API response types.
 * These mirror the backend response contracts.
 */

export interface Person {
  id: number;
  name: string;
  height: string;
  mass: string;
  birth_year: string;
  gender: string;
}

export interface PersonResponse {
  data: Person;
}

export interface TopQuery {
  person_id: number;
  count: number;
  percentage: number;
}

export interface QueryStatistics {
  top_queries: TopQuery[];
  average_response_time_ms: number;
  popular_hours: Record<number, number>;
  total_queries: number;
  computed_at: string;
}

export interface StatisticsResponse {
  data: QueryStatistics;
}

export interface HealthResponse {
  status: string;
}
