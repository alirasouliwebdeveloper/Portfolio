import Link from "next/link";
import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { ChevronLeft, ChevronRight } from "./icons";

type PaginationProps = {
  current: number;
  total: number;
  href: (page: number) => string;
  className?: string;
};

export function paginationItems(
  current: number,
  total: number,
): (number | "gap")[] {
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);

  const middle =
    current <= 2
      ? [1, 2, 3]
      : current >= total - 1
        ? [total - 2, total - 1, total]
        : [current - 1, current, current + 1];
  const pages = [...new Set([1, ...middle, total])].sort((a, b) => a - b);

  const items: (number | "gap")[] = [];
  pages.forEach((page, index) => {
    if (index > 0 && page - pages[index - 1] > 1) items.push("gap");
    items.push(page);
  });
  return items;
}

const item =
  "flex h-11 min-w-11 items-center justify-center gap-2 rounded-control px-3.5 text-sm";
const idle =
  "border border-border text-text-2 transition-[color,border-color,scale] duration-300 ease-out hover:border-accent hover:scale-105";

export function Pagination({
  current,
  total,
  href,
  className,
}: PaginationProps) {
  if (total <= 1) return null;

  const step = (
    label: string,
    page: number,
    disabled: boolean,
    icon: ReactNode,
    first: boolean,
  ) =>
    disabled ? (
      <span aria-disabled="true" className={cn(item, idle, "opacity-40")}>
        {first ? icon : null}
        {label}
        {first ? null : icon}
      </span>
    ) : (
      <Link
        href={href(page)}
        rel={first ? "prev" : "next"}
        className={cn(item, idle)}
      >
        {first ? icon : null}
        {label}
        {first ? null : icon}
      </Link>
    );

  return (
    <nav
      aria-label="Pagination"
      className={cn("flex items-center justify-center gap-2.5", className)}
    >
      {step(
        "Previous",
        current - 1,
        current <= 1,
        <ChevronLeft className="size-4" />,
        true,
      )}

      <ul className="tablet:flex hidden items-center gap-2.5">
        {paginationItems(current, total).map((entry, index) =>
          entry === "gap" ? (
            <li
              key={`gap-${index}`}
              aria-hidden="true"
              className="text-faint px-1"
            >
              …
            </li>
          ) : (
            <li key={entry}>
              {entry === current ? (
                <span
                  aria-current="page"
                  className={cn(item, "bg-accent font-semibold text-white")}
                >
                  {entry}
                </span>
              ) : (
                <Link
                  href={href(entry)}
                  aria-label={`Page ${entry}`}
                  className={cn(item, idle)}
                >
                  {entry}
                </Link>
              )}
            </li>
          ),
        )}
      </ul>
      <span className="text-text-2 tablet:hidden px-2 text-sm">
        Page {current} of {total}
      </span>

      {step(
        "Next",
        current + 1,
        current >= total,
        <ChevronRight className="size-4" />,
        false,
      )}
    </nav>
  );
}
