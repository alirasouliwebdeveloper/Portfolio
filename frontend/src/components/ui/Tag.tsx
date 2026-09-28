import type { ReactNode } from "react";
import { cn } from "@/lib/cn";

type TagProps = {
  size?: "md" | "sm";
  className?: string;
  children: ReactNode;
};

export function Tag({ size = "md", className, children }: TagProps) {
  return (
    <span
      className={cn(
        "bg-chip text-chip-text",
        size === "md"
          ? "rounded-tag text-caption px-2.75 py-1.5"
          : "rounded-tag-sm px-2.25 py-1 text-xs",
        className,
      )}
    >
      {children}
    </span>
  );
}
