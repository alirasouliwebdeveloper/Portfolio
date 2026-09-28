import type { ReactNode } from "react";
import { cn } from "@/lib/cn";

type EyebrowProps = {
  variant?: "text" | "chip";
  className?: string;
  children: ReactNode;
};

export function Eyebrow({
  variant = "text",
  className,
  children,
}: EyebrowProps) {
  return (
    <div
      className={cn(
        "font-semibold uppercase",
        variant === "text"
          ? "text-kicker tracking-eyebrow text-eyebrow"
          : "rounded-tag bg-chip tracking-chip text-icon-soft inline-block px-3 py-1.75 text-xs",
        className,
      )}
    >
      {children}
    </div>
  );
}
