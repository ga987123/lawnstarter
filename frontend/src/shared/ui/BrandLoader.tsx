interface BrandLoaderProps {
  label?: string;
}

export function BrandLoader({ label }: BrandLoaderProps) {
  return (
    <div className="flex flex-col items-center justify-center gap-3 py-12">
      <div
        className="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-[var(--color-brand)]"
        aria-hidden
      />
      {label && (
        <p className="text-sm text-slate-600">{label}</p>
      )}
    </div>
  );
}
