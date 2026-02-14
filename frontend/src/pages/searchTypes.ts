export const SearchType = {
  People: "people",
  Movies: "movies",
} as const;

export type SearchTypeValue = (typeof SearchType)[keyof typeof SearchType];
