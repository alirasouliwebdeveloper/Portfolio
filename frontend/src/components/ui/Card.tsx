import type { ElementType, ReactNode } from "react";
import { cn } from "@/lib/cn";

type CardProps = {
  as?: ElementType;
  tone?: "surface" | "strong";
  radius?: "card" | "lg";
  padded?: boolean;
  className?: string;
  children: ReactNode;
};

export function Card({
  as: Tag = "div",
  tone = "surface",
  radius = "card",
  padded = true,
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
        className,
      )}
    >
      {children}
    </Tag>
  );
}
