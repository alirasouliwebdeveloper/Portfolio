import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import type { HomeContent, Testimonial } from "@/types/api";
import { TestimonialCard } from "./TestimonialCard";

export function TestimonialsSection({
  content,
  testimonials,
}: {
  content: HomeContent["testimonials"];
  testimonials: Testimonial[];
}) {
  if (testimonials.length === 0) return null;

  return (
    <Section aria-labelledby="testimonials-title">
      <SectionHeading
        eyebrow={content.eyebrow}
        title={content.heading}
        id="testimonials-title"
      />
      <div className="gap-grid desktop:grid-cols-3 mt-12 grid">
        {testimonials.map((testimonial) => (
          <TestimonialCard key={testimonial.name} testimonial={testimonial} />
        ))}
      </div>
    </Section>
  );
}
