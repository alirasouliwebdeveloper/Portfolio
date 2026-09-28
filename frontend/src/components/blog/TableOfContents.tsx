"use client";

import { useEffect, useState } from "react";
import { cn } from "@/lib/cn";
import type { TocItem } from "@/types/api";
import { ChevronDown } from "@/components/ui/icons";

/** "On this page": sticky on desktop, a collapsible box on tablet/mobile; highlights the section being read. */
export function TableOfContents({ items }: { items: TocItem[] }) {
  const [active, setActive] = useState(items[0]?.id ?? "");

  useEffect(() => {
    const headings = items
      .map((item) => document.getElementById(item.id))
      .filter((el): el is HTMLElement => Boolean(el));
    if (headings.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (visible[0]) setActive(visible[0].target.id);
      },
      { rootMargin: "-96px 0px -65% 0px" },
    );
    headings.forEach((heading) => observer.observe(heading));
    return () => observer.disconnect();
  }, [items]);

  if (items.length === 0) return null;

  const list = (
    <ul className="border-divider flex flex-col border-s">
      {items.map((item) => (
        <li key={item.id}>
          <a
            href={`#${item.id}`}
            aria-current={active === item.id ? "location" : undefined}
            className={cn(
              "text-caption -ms-px block border-s-2 py-2 ps-4 transition-colors",
              active === item.id
                ? "border-accent text-text font-medium"
                : "text-muted hover:text-text border-transparent",
            )}
          >
            {item.text}
          </a>
        </li>
      ))}
    </ul>
  );

  return (
    <>
      <nav
        aria-label="On this page"
        className="desktop:block sticky top-24 hidden"
      >
        <h2 className="tracking-eyebrow text-eyebrow mb-4 text-xs font-semibold uppercase">
          On this page
        </h2>
        {list}
      </nav>

      <details className="group rounded-card border-border bg-surface desktop:hidden border">
        <summary className="text-ui text-text flex cursor-pointer list-none items-center justify-between px-5 py-4 font-semibold">
          On this page
          <ChevronDown className="size-4 transition-transform group-open:rotate-180" />
        </summary>
        <nav aria-label="On this page" className="px-5 pb-4">
          {list}
        </nav>
      </details>
    </>
  );
}
