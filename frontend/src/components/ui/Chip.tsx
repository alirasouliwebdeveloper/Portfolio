import Link from "next/link";
import type { ButtonHTMLAttributes, ReactNode } from "react";
import { cn } from "@/lib/cn";

type ChipProps = {
  active?: boolean;
  count?: number;
  className?: string;
  children: ReactNode;
} & (
  | { href: string }
  | ({ href?: undefined } & Omit<
      ButtonHTMLAttributes<HTMLButtonElement>,
      "className"
    >)
);

export function FilterChip(props: ChipProps) {
  const { active, count, className, children, ...rest } = props;
  const classes = cn(
    "inline-flex items-center gap-1.5 rounded-pill px-4.5 py-2.5 text-sm transition-colors",
    active
      ? "bg-accent font-medium text-white"
      : "border border-border-input text-text-2 hover:border-accent",
    className,
  );
  const content = (
    <>
      {children}
      {count !== undefined ? (
        <span className={active ? undefined : "opacity-70"}>{count}</span>
      ) : null}
    </>
  );

  if (rest.href !== undefined) {
    return (
      <Link
        href={rest.href}
        className={classes}
        aria-current={active ? "page" : undefined}
      >
        {content}
      </Link>
    );
  }

  const { type = "button", ...button } = rest;
  return (
    <button type={type} className={classes} aria-pressed={active} {...button}>
      {content}
    </button>
  );
}
