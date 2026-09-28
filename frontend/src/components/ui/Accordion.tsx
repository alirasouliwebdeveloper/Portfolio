"use client";

import { useId, useState, type ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Icon } from "./icons";

export type AccordionItem = {
  id: string;
  question: string;
  answer: ReactNode;
};

type AccordionProps = {
  items: AccordionItem[];
  defaultOpenId?: string | null;
  className?: string;
};

export function Accordion({ items, defaultOpenId, className }: AccordionProps) {
  const [openId, setOpenId] = useState<string | null>(
    defaultOpenId === undefined ? (items[0]?.id ?? null) : defaultOpenId,
  );
  const base = useId();

  return (
    <div className={cn("flex flex-col gap-3.5", className)}>
      {items.map((item) => {
        const open = item.id === openId;
        const buttonId = `${base}-${item.id}-button`;
        const panelId = `${base}-${item.id}-panel`;
        return (
          <div
            key={item.id}
            className={cn(
              "rounded-card border transition-colors",
              open
                ? "border-accent-border bg-surface-strong"
                : "border-border bg-surface",
            )}
          >
            <h3>
              <button
                id={buttonId}
                type="button"
                aria-expanded={open}
                aria-controls={panelId}
                onClick={() => setOpenId(open ? null : item.id)}
                className="rounded-card text-card-title text-text flex w-full items-center justify-between gap-4 px-6 py-5 text-start font-semibold"
              >
                {item.question}
                <span
                  className={cn(
                    "rounded-pill flex size-9 shrink-0 items-center justify-center text-white transition-colors",
                    open ? "bg-accent" : "bg-border",
                  )}
                >
                  <Icon name={open ? "minus" : "plus"} className="size-4.5" />
                </span>
              </button>
            </h3>
            <div
              id={panelId}
              role="region"
              aria-labelledby={buttonId}
              inert={!open}
              className={cn(
                "grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none",
                open ? "grid-rows-[1fr]" : "grid-rows-[0fr]",
              )}
            >
              <div className="overflow-hidden">
                <div className="text-body text-muted px-6 pb-6">
                  {item.answer}
                </div>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}
