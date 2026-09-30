import type { ElementType, ReactNode } from "react";
import { cn } from "@/lib/cn";

type CardProps = {
  as?: ElementType;
  tone?: "surface" | "strong";
  radius?: "card" | "lg";
  padded?: boolean;
  /** Lift + scale + shadow on hover (see `.hover-card` in globals.css); off by default for cards that host a form or other non-tile content. */
  hover?: boolean;
  className?: string;
  children: ReactNode;
};

export function Card({
  as: Tag = "div",
  tone = "surface",
  radius = "card",
  padded = true,
  hover = false,
  className,
  children,
}: CardProps) {
  return (
    <Tag
      className={cn(
        "border",
        tone === "strong"
          ? "border-accent-border bg-surface-strong"
          : "border-border bg-surface",
        radius === "lg" ? "rounded-card-lg" : "rounded-card",
        padded && "p-card",
        hover && "hover-card",
        className,
      )}
    >
      {children}
    </Tag>
  );
}
