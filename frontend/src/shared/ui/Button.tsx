import type { ButtonHTMLAttributes } from "react";

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  loading?: boolean;
  loadingText?: string;
}

export function Button({
  children,
  loading,
  loadingText = "Loading...",
  disabled,
  className = "",
  ...props
}: ButtonProps) {
  return (
    <button
      disabled={disabled || loading}
      className={`rounded-full border border-[.5px] bg-[var(--color-brand)] px-5 py-2.5 text-sm font-bold text-white hover:bg-[var(--color-brand-hover)] disabled:opacity-50 ${className}`}
      {...props}
    >
      {loading ? loadingText : children}
    </button>
  );
}
