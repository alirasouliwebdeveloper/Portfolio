import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { Media } from "@/components/ui/Media";
import { ArrowUpRight } from "@/components/ui/icons";
import type { AboutContent, Service } from "@/types/api";

export function ServiceHero({
  service,
  trust,
}: {
  service: Service;
  trust: AboutContent["trust"];
}) {
  return (
    <section
      aria-labelledby="service-title"
      className="border-line-soft bg-bg desktop:pb-24 border-b pt-10 pb-16"
    >
      <Container>
        <Breadcrumbs
          items={[
            { label: "Home", href: "/" },
            { label: "Services" },
            { label: service.nav_label },
          ]}
        />

        <div className="desktop:mt-14 desktop:grid-cols-[42.5rem_minmax(0,1fr)] desktop:gap-20 mt-10 grid items-center gap-14">
          <div className="flex flex-col items-start">
            <div className="flex items-center gap-3">
              <IconTile
                name={service.icon}
                tone="accent"
                size="sm"
                className="size-11"
              />
              <Eyebrow>Service</Eyebrow>
            </div>
            <h1
              id="service-title"
              className="text-page-h1 tracking-h1 text-text mt-6 leading-[1.1]"
            >
              {service.h1}
            </h1>
            <p className="text-lead text-muted mt-6 leading-[1.7]">
              {service.lead}
            </p>

            <div className="tablet:w-auto tablet:flex-row mt-9 flex w-full flex-col gap-4">
              <Button
                href={`/contact?service=${service.slug}`}
                fullWidth
                className="tablet:w-auto"
              >
                Get a Free Quote <ArrowUpRight className="size-3.5" />
              </Button>
              {service.related_project ? (
                <Button
                  href="#work"
                  variant="outline"
                  fullWidth
                  className="tablet:w-auto"
                >
                  See Related Work
                </Button>
              ) : null}
            </div>

            {trust.length > 0 ? (
              <ul className="border-divider mt-12 flex w-full flex-wrap justify-between gap-x-8 gap-y-5 border-t pt-8">
                {trust.map((item) => (
                  <li key={item.label} className="flex items-center gap-3.5">
                    <IconTile
                      name={item.icon}
                      tone="chip"
                      size="sm"
                      className="size-11.5"
                    />
                    <div>
                      <div className="text-text text-xl font-semibold">
                        {item.value}
                      </div>
                      <div className="text-caption text-dim">{item.label}</div>
                    </div>
                  </li>
                ))}
              </ul>
            ) : null}
          </div>

          <div className="relative">
            <div className="hover-card rounded-card-lg border-border bg-surface shadow-float relative aspect-16/11 overflow-hidden border">
              <Media
                image={service.hero_image}
                size="lg"
                fill
                sheen
                priority
                sizes="(min-width: 1280px) 45vw, 100vw"
              />
            </div>
            {service.floating_metric ? (
              <div className="hover-card rounded-card border-border bg-surface shadow-float max-desktop:start-4 absolute -start-10 -bottom-9 flex items-center gap-4 border px-6 py-5">
                <IconTile
                  name="trend"
                  tone="success"
                  size="sm"
                  className="size-12"
                />
                <div>
                  <div className="text-text text-[1.625rem] leading-tight font-bold">
                    {service.floating_metric.value}
                  </div>
                  <div className="text-caption text-muted">
                    {service.floating_metric.label}
                  </div>
                </div>
              </div>
            ) : null}
          </div>
        </div>
      </Container>
    </section>
  );
}
