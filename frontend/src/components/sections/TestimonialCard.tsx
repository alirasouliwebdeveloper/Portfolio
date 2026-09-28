import Image from "next/image";
import { Star } from "lucide-react";
import type { Testimonial } from "@/types/api";

export function TestimonialCard({ testimonial }: { testimonial: Testimonial }) {
  const byline = [testimonial.role, testimonial.company]
    .filter(Boolean)
    .join(", ");

  return (
    <figure className="rounded-card border-border bg-surface p-card flex flex-col gap-5.5 border">
      <svg
        width="34"
        height="28"
        viewBox="0 0 36 30"
        fill="currentColor"
        aria-hidden="true"
        className="text-accent-soft"
      >
        <path d="M0 30V17C0 7 5 1 14 0v6c-4 1-6 4-6 8h6v16H0Zm20 0V17c0-10 5-16 14-17v6c-4 1-6 4-6 8h6v16H20Z" />
      </svg>
      <div
        className="text-star flex gap-1"
        role="img"
        aria-label={`${testimonial.rating} out of 5 stars`}
      >
        {Array.from({ length: 5 }, (_, index) => (
          <Star
            key={index}
            className="size-4"
            fill={index < testimonial.rating ? "currentColor" : "none"}
            strokeWidth={1.5}
            aria-hidden="true"
          />
        ))}
      </div>
      <blockquote className="text-body text-text-2 grow">
        {testimonial.quote}
      </blockquote>
      <figcaption className="border-divider flex items-center gap-3.5 border-t pt-5">
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
            <div className="text-caption text-muted">{byline}</div>
          ) : null}
        </div>
      </figcaption>
    </figure>
  );
}
