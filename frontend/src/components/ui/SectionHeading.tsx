import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Eyebrow } from "./Eyebrow";

type SectionHeadingProps = {
  eyebrow?: string;
  title: ReactNode;
  description?: ReactNode;
  align?: "center" | "start";
  as?: "h1" | "h2";
  id?: string;
  className?: string;
};

export function SectionHeading({
  eyebrow,
  title,
  description,
  align = "center",
  as: Heading = "h2",
  id,
  className,
}: SectionHeadingProps) {
  return (
    <div
      className={cn(
        "flex flex-col",
        align === "center"
          ? "items-center text-center"
          : "items-start text-start",
        className,
      )}
    >
      {eyebrow ? <Eyebrow>{eyebrow}</Eyebrow> : null}
      <Heading id={id} className="text-h2 tracking-h2 text-text mt-2.5">
        {title}
      </Heading>
      {description ? (
        <p className="text-subhead text-muted mt-3.5 max-w-160">
          {description}
        </p>
      ) : null}
    </div>
  );
}
