import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { RichContent } from "@/components/ui/RichContent";
import { cn } from "@/lib/cn";
import type { HomeContent, Settings } from "@/types/api";
import { User } from "lucide-react";

export function AboutTeaser({
  content,
  stats,
}: {
  content: HomeContent["about_teaser"];
  stats: Settings["stats"];
}) {
  return (
    <section
      aria-labelledby="about-teaser-title"
      className="bg-bg-alt py-section"
    >
      <Container className="desktop:flex-row desktop:items-center desktop:justify-between flex flex-col gap-12">
        <div className="desktop:w-160 flex flex-col items-start">
          <Eyebrow variant="chip">{content.eyebrow}</Eyebrow>
          <h2
            id="about-teaser-title"
            className="text-h2 tracking-h2 text-text mt-5 leading-[1.3]"
          >
            {content.heading}
          </h2>
          <RichContent html={content.text} className="mt-5 leading-[1.75]" />
          <Button href="/about" variant="outline" size="sm" className="mt-7.5">
            Learn More About Me{" "}
            <User className="size-4" strokeWidth={1.8} aria-hidden="true" />
          </Button>
        </div>

        <dl className="desktop:w-175 grid grid-cols-2">
          {stats.map((stat, index) => (
            <div
              key={stat.label}
              className={cn(
                "border-divider tablet:flex-row tablet:items-center tablet:gap-5 tablet:p-7.5 desktop:px-8 flex flex-col items-start gap-3 px-4 py-5",
                index % 2 === 0 && "border-e",
                index < 2 && "border-b",
              )}
            >
              <IconTile
                name={stat.icon}
                tone={index === 0 || index === 3 ? "accent" : "tile"}
              />
              <div>
                <dd className="text-stat text-text leading-tight font-semibold">
                  {stat.value}
                </dd>
                <dt className="text-meta text-muted">{stat.label}</dt>
              </div>
            </div>
          ))}
        </dl>
      </Container>
    </section>
  );
}
