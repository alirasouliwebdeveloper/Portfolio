import Image from "next/image";
import type { Testimonial } from "@/types/api";

/** The large client quote at the end of a case study. */
export function QuoteBlock({ testimonial }: { testimonial: Testimonial }) {
  const byline = [testimonial.role, testimonial.company]
    .filter(Boolean)
    .join(", ");

  return (
    <figure className="hover-card rounded-card border-border bg-surface tablet:p-10 desktop:flex-row desktop:items-start desktop:gap-8 desktop:p-12 flex flex-col gap-6 border p-6">
      <svg
        width="44"
        height="36"
        viewBox="0 0 36 30"
        fill="currentColor"
        aria-hidden="true"
        className="text-accent-soft shrink-0"
      >
        <path d="M0 30V17C0 7 5 1 14 0v6c-4 1-6 4-6 8h6v16H0Zm20 0V17c0-10 5-16 14-17v6c-4 1-6 4-6 8h6v16H20Z" />
      </svg>
      <div className="flex flex-col gap-6">
        <blockquote className="text-code tablet:text-[1.4375rem] text-[1.25rem] leading-[1.6]">
          {testimonial.quote}
        </blockquote>
        <figcaption className="flex items-center gap-3.5">
          {testimonial.avatar ? (
            <Image
              src={testimonial.avatar.card ?? testimonial.avatar.url}
              alt=""
              width={48}
              height={48}
              className="size-12 rounded-full object-cover"
            />
          ) : (
            <span className="bg-tile text-ui text-chip-text flex size-12 shrink-0 items-center justify-center rounded-full font-semibold">
              {testimonial.initials}
            </span>
          )}
          <div>
            <div className="text-ui text-text font-semibold">
              {testimonial.name}
            </div>
            {byline ? (
              <div className="text-meta text-muted">{byline}</div>
            ) : null}
          </div>
        </figcaption>
      </div>
    </figure>
  );
}
