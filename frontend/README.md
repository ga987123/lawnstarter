# Frontend - SWAPI Proxy UI

React 19 + TypeScript + Vite single-page application that consumes the SWAPI Proxy backend API. You can search for people and films and open detail pages for each.

**Stack:** Vite 7, React 19, React Router 7, TanStack Query 5, Tailwind 4.

## Prerequisites

- **Node.js** (LTS recommended)
- **npm**

## Setup

```bash
cd frontend
npm install
```

## Scripts

| Script | Description |
|--------|-------------|
| `npm run dev` | Start Vite dev server (default port 5173) |
| `npm run build` | Type-check and build for production |
| `npm run preview` | Serve production build locally |
| `npm run test` | Run tests in watch mode |
| `npm run test:run` | Run tests once (CI-friendly) |
| `npm run test:coverage` | Run tests with coverage (output in `coverage/`) |
| `npm run lint` | Run ESLint |
| `npm run format` | Format code with Prettier |
| `npm run format:check` | Check formatting only |

From the **project root** (with Docker):

- `make test-frontend` — runs `npm run test:run` in the frontend container  
- `make test-frontend-coverage` — runs `npm run test:coverage` in the frontend container  

## Architecture

The app uses a clear separation between app bootstrap, pages, shared UI/API, and SWAPI services.


### Module boundaries

| Module | Responsibility | May import from |
|--------|----------------|-----------------|
| `app/` | Bootstrap, root component, providers, routing | pages, shared |
| `pages/` | Route-level pages and page-specific components | services, shared |
| `services/swapi/` | SWAPI API hooks and types | shared |
| `shared/` | Reusable UI, API client, base types | — |

### Folder structure

```
src/
├── app/                        # Application bootstrap
│   ├── main.tsx                # Entry point
│   ├── App.tsx                 # Root component and routes
│   ├── index.css               # Global styles (Tailwind)
│   ├── providers.tsx           # QueryClientProvider
│   └── __tests__/
├── pages/                      # Route-level pages
│   ├── SearchPage.tsx          # Search (people/films)
│   ├── PersonDetailPage.tsx
│   ├── FilmDetailPage.tsx
│   ├── searchTypes.ts
│   ├── components/             # Page-specific components
│   │   ├── SearchForm.tsx
│   │   ├── SearchResults.tsx
│   │   ├── SearchQueryInput.tsx
│   │   ├── SearchTypeInput.tsx
│   │   ├── MovieCard.tsx
│   │   ├── PersonCard.tsx
│   │   └── __tests__/
│   └── __tests__/
├── services/
│   └── swapi/
│       ├── api/
│       │   ├── queries.ts      # TanStack Query hooks
│       │   └── __tests__/
│       └── types.ts
├── shared/
│   ├── api/
│   │   ├── client.ts           # Typed fetch wrapper
│   │   ├── types.ts
│   │   └── __tests__/
│   └── ui/
│       ├── Button.tsx
│       ├── BrandLoader.tsx
│       ├── RelatedLinks.tsx
│       └── __tests__/
└── __tests__/
    ├── setup.ts                # Global test setup (e.g. jest-dom)
    └── utils.tsx               # Render helpers (MemoryRouter, QueryClientProvider)
```

## Routes

| Path | Page |
|------|------|
| `/` | Search (people or films) |
| `/person/:id` | Person detail |
| `/film/:id` | Film detail |

## Why TanStack Query?

We use [TanStack Query](https://tanstack.com/query) for server state because it provides:

- **Caching and deduplication** of API responses  
- **Loading and error states** (`isLoading`, `isError`, `data`)  
- **Stale-while-revalidate** behavior  
- **Query invalidation** for refetching  
- **DevTools** for debugging  

The API client in `shared/api/client.ts` is a thin typed wrapper around `fetch`; TanStack Query handles the async lifecycle.

## Development

The Vite dev server runs on port 5173 and proxies `/api/*` to the backend so you don’t need CORS setup:

```
Browser → http://localhost:5173         → Vite dev server
Browser → http://localhost:5173/api/*   → nginx → php-fpm
```

## Testing

Tests use **Vitest 4** and **React Testing Library**.

- **Co-located tests:** `__tests__` folders live next to the code (e.g. `shared/ui/__tests__/Button.test.tsx`).
- **Global setup:** `src/__tests__/setup.ts` (e.g. `@testing-library/jest-dom`).
- **Test utils:** `src/__tests__/utils.tsx` provides a custom render with `MemoryRouter` and `QueryClientProvider` for components that need routing or queries.

```bash
npm run test          # Watch mode
npm run test:run      # Single run (CI)
npm run test:coverage # With coverage (report in coverage/)
```

Coverage is configured in `vite.config.ts` via `@vitest/coverage-v8`; the `coverage/` directory is gitignored.
<img width="1674" height="942" alt="image" src="https://github.com/user-attachments/assets/fa59861d-8148-447f-9c28-9c8a46faccd7" />

## Code quality

| Tool | Config | Purpose |
|------|--------|---------|
| TypeScript | `tsconfig.app.json` | Strict type checking |
| ESLint | `eslint.config.js` | Linting |
| Prettier | `.prettierrc` | Formatting |
| Vitest | `vite.config.ts` | Tests and coverage |
