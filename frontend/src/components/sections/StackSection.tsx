import Link from "next/link";
import { Card } from "@/components/ui/Card";
import { IconTile } from "@/components/ui/IconTile";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Tag } from "@/components/ui/Tag";
import { ArrowUpRight } from "@/components/ui/icons";
import type { HomeContent, ServiceSummary } from "@/types/api";

export function StackSection({
  content,
  services,
}: {
  content: HomeContent["stack"];
  services: ServiceSummary[];
}) {
  const bySlug = new Map(services.map((service) => [service.slug, service]));

  return (
    <Section aria-labelledby="stack-title">
      <SectionHeading
        eyebrow={content.eyebrow}
        title={content.heading}
        description={content.text}
        id="stack-title"
      />

      <div className="tablet:gap-6 tablet:grid-cols-2 desktop:grid-cols-4 mt-12 grid gap-5">
        {content.groups.map((group) => {
          const service = group.service_slug
            ? bySlug.get(group.service_slug)
            : undefined;
          return (
            <Card key={group.title} className="flex flex-col gap-4">
              <div className="flex items-center gap-4">
                <IconTile name={group.icon} />
                <h3 className="text-card-title-lg text-text font-semibold">
                  {group.title}
                </h3>
              </div>
              <p className="text-ui text-muted leading-relaxed">{group.text}</p>
              <ul className="mt-auto flex flex-wrap gap-2">
                {group.tags.map((tag) => (
                  <li key={tag}>
                    <Tag>{tag}</Tag>
                  </li>
                ))}
              </ul>
              {service ? (
                <Link
                  href={`/services/${service.slug}`}
                  className="border-divider text-link flex items-center gap-2 border-t pt-3.5 text-sm"
                >
                  {service.nav_label} <ArrowUpRight className="size-3.5" />
                </Link>
              ) : null}
            </Card>
          );
        })}
      </div>
    </Section>
  );
}
