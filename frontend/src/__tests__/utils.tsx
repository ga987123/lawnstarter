import { render, type RenderOptions } from "@testing-library/react";
import { MemoryRouter, type MemoryRouterProps } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import type { ReactElement, ReactNode } from "react";

const defaultQueryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: false },
  },
});

interface AllTheProvidersProps {
  children: ReactNode;
  routerProps?: MemoryRouterProps;
  queryClient?: QueryClient;
}

function AllTheProviders({
  children,
  routerProps = {},
  queryClient = defaultQueryClient,
}: AllTheProvidersProps) {
  return (
    <QueryClientProvider client={queryClient}>
      <MemoryRouter {...routerProps}>{children}</MemoryRouter>
    </QueryClientProvider>
  );
}

interface CustomRenderOptions extends Omit<RenderOptions, "wrapper"> {
  routerProps?: MemoryRouterProps;
  queryClient?: QueryClient;
}

function customRender(
  ui: ReactElement,
  options: CustomRenderOptions = {},
) {
  const { routerProps, queryClient, ...renderOptions } = options;
  return render(ui, {
    wrapper: ({ children }) => (
      <AllTheProviders routerProps={routerProps} queryClient={queryClient}>
        {children}
      </AllTheProviders>
    ),
    ...renderOptions,
  });
}

export { customRender as render, defaultQueryClient, AllTheProviders };
