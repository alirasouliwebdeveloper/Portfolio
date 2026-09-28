import type { ReactNode } from "react";
import { Section } from "@/components/ui/Section";

/** "Have a project in mind?" band used at the end of About and service pages. */
export function CtaBand({
  heading,
  text,
  children,
}: {
  heading: string;
  text: string;
  children: ReactNode;
}) {
  return (
    <Section aria-labelledby="cta-title">
      <div className="rounded-card-lg border-accent-border/60 bg-tile tablet:flex-row tablet:items-center tablet:justify-between tablet:p-12 desktop:px-14 desktop:py-13 flex flex-col gap-8 border p-8">
        <div>
          <h2
            id="cta-title"
            className="text-text tablet:text-[2.375rem] text-[1.75rem] leading-tight font-semibold"
          >
            {heading}
          </h2>
          <p className="text-subhead text-text-2 mt-3 max-w-150">{text}</p>
        </div>
        <div className="tablet:flex-row flex flex-col gap-3">{children}</div>
      </div>
    </Section>
  );
}
