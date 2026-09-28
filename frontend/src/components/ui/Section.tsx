import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Container } from "./Container";

type SectionProps = {
  variant?: "base" | "alt";
  bordered?: boolean;
  flush?: boolean;
  id?: string;
  className?: string;
  containerClassName?: string;
  "aria-labelledby"?: string;
  children: ReactNode;
};

export function Section({
  variant = "base",
  bordered,
  flush,
  id,
  className,
  containerClassName,
  children,
  ...aria
}: SectionProps) {
  return (
    <section
      id={id}
      className={cn(
        variant === "alt" ? "bg-bg-alt" : "bg-bg",
        !flush && "py-section",
        bordered && "border-line-soft border-b",
        className,
      )}
      {...aria}
    >
      <Container className={containerClassName}>{children}</Container>
    </section>
  );
}
