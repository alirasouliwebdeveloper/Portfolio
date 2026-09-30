import Link from "next/link";
import { PostCard } from "@/components/blog/PostCard";
import { Accordion } from "@/components/ui/Accordion";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { Icon } from "@/components/ui/icons";
import { Media } from "@/components/ui/Media";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Tag } from "@/components/ui/Tag";
import { ArrowUpRight } from "@/components/ui/icons";
import type { Service, ServiceSummary } from "@/types/api";

export function PainSection({ service }: { service: Service }) {
  if (service.pains.length === 0) return null;
  return (
    <Section variant="alt" aria-labelledby="pain-title">
      <SectionHeading
        eyebrow="Sound familiar?"
        title="Is this for you?"
        id="pain-title"
      />
      <div className="gap-grid desktop:grid-cols-3 mt-12 grid">
        {service.pains.map((pain) => (
          <Card key={pain.title} hover className="flex flex-col gap-4">
            <IconTile name={pain.icon} tone="danger" />
            <h3 className="text-text text-[1.3125rem] leading-[1.35] font-semibold">
              {pain.title}
            </h3>
            <p className="text-body text-muted">{pain.text}</p>
          </Card>
        ))}
      </div>
    </Section>
  );
}

export function OffersSection({ service }: { service: Service }) {
  if (service.offers.length === 0) return null;
  return (
    <Section aria-labelledby="offers-title">
      <SectionHeading
        eyebrow="What you get"
        title={`${service.title} — what's included`}
        id="offers-title"
      />
      <div className="tablet:grid-cols-2 tablet:gap-6 desktop:grid-cols-3 mt-12 grid gap-4">
        {service.offers.map((offer) => (
          <Card
            key={offer.title}
            hover
            className="desktop:p-6 flex items-start gap-5 p-5"
          >
            <IconTile name={offer.icon} tone="chip" size="sm" />
            <div>
              <h3 className="text-ui text-text font-semibold">{offer.title}</h3>
              <p className="text-caption text-muted mt-1 leading-relaxed">
                {offer.text}
              </p>
            </div>
          </Card>
        ))}
      </div>
    </Section>
  );
}

export function StackSection({ service }: { service: Service }) {
  return (
    <Section variant="alt" aria-labelledby="why-title">
      <div className="desktop:grid-cols-2 desktop:items-center desktop:gap-24 grid gap-10">
        <div>
          <Eyebrow>The stack</Eyebrow>
          <h2 id="why-title" className="text-h2 text-text mt-2.5 leading-tight">
            {service.why.title}
          </h2>
          {service.why.text_html ? (
            <RichContent
              html={service.why.text_html}
              className="text-subhead mt-5 leading-[1.75]"
            />
          ) : null}
          {service.why.points.length > 0 ? (
            <ul className="mt-7 flex flex-col gap-4">
              {service.why.points.map((point) => (
                <li
                  key={point}
                  className="text-subhead text-code flex items-center gap-3.5"
                >
                  <span className="bg-success-tile text-success flex size-7.5 shrink-0 items-center justify-center rounded-full">
                    <Icon name="check" className="size-4" />
                  </span>
                  {point}
                </li>
              ))}
            </ul>
          ) : null}
        </div>

        <Card hover className="desktop:p-11 flex flex-col p-8">
          <h3 className="text-text text-[1.1875rem] font-semibold">
            Tools I use for this
          </h3>
          <ul className="mt-5.5 flex flex-wrap gap-2.5">
            {service.stack.map((tool) => (
              <li key={tool}>
                <Tag size="xl">{tool}</Tag>
              </li>
            ))}
          </ul>
          <Link
            href="/about"
            className="border-divider text-ui text-link mt-6 flex items-center gap-2 border-t pt-5"
          >
            More about how I work <ArrowUpRight className="size-3.5" />
          </Link>
        </Card>
      </div>
    </Section>
  );
}

export function WorkSection({ service }: { service: Service }) {
  const project = service.related_project;
  if (!project) return null;
  const testimonial = project.testimonial;

  return (
    <Section id="work" variant="alt" aria-labelledby="work-title">
      <div className="flex items-end justify-between gap-4">
        <div>
          <Eyebrow>Case study</Eyebrow>
          <h2 id="work-title" className="text-h2 tracking-h2 text-text mt-2.5">
            Related work
          </h2>
        </div>
        <Button
          href="/#projects"
          variant="outline"
          className="text-ui max-tablet:hidden h-11.5"
        >
          All Projects <ArrowUpRight className="size-3.5" />
        </Button>
      </div>

      <Link
        href={`/projects/${project.slug}`}
        className="hover-card rounded-card border-border bg-surface desktop:grid-cols-[1.2fr_1fr] mt-12 grid overflow-hidden border"
      >
        <div className="relative aspect-16/10">
          <Media
            image={project.cover}
            size="md"
            fill
            sheen
            sizes="(min-width: 1280px) 55vw, 100vw"
          />
        </div>
        <div className="desktop:p-14 flex flex-col justify-center gap-4.5 p-8">
          <span className="text-meta text-dim">Case study</span>
          <h3 className="text-h2 text-text leading-tight font-semibold">
            {project.title}
          </h3>
          <p className="text-subhead text-muted">{project.summary}</p>
          {project.metrics.length > 0 ? (
            <dl className="mt-2 flex flex-wrap gap-x-8 gap-y-3">
              {project.metrics.slice(0, 3).map((metric) => (
                <div key={metric.label}>
                  <dd className="text-text text-[1.75rem] leading-tight font-bold">
                    {metric.value}
                  </dd>
                  <dt className="text-caption text-dim">{metric.label}</dt>
                </div>
              ))}
            </dl>
          ) : null}
          <span className="text-ui text-link mt-2 flex items-center gap-2">
            Read the case study <ArrowUpRight className="size-3.5" />
          </span>
        </div>
      </Link>

      {testimonial ? (
        <figure className="hover-card rounded-card border-border bg-surface tablet:flex-row tablet:items-center tablet:gap-8 tablet:px-12 tablet:py-10 mt-8 flex flex-col gap-6 border p-6">
          <svg
            width="40"
            height="32"
            viewBox="0 0 36 30"
            fill="currentColor"
            aria-hidden="true"
            className="text-accent-soft shrink-0"
          >
            <path d="M0 30V17C0 7 5 1 14 0v6c-4 1-6 4-6 8h6v16H0Zm20 0V17c0-10 5-16 14-17v6c-4 1-6 4-6 8h6v16H20Z" />
          </svg>
          <blockquote className="text-code tablet:text-xl grow text-lg leading-[1.6]">
            {testimonial.quote}
          </blockquote>
          <figcaption className="flex shrink-0 items-center gap-3.5">
            <span className="bg-tile text-ui text-chip-text flex size-12.5 items-center justify-center rounded-full font-semibold">
              {testimonial.initials}
            </span>
            <div>
              <div className="text-text text-base font-semibold">
                {testimonial.name}
              </div>
              <div className="text-meta text-muted">
                {[testimonial.role, testimonial.company]
                  .filter(Boolean)
                  .join(", ")}
              </div>
            </div>
          </figcaption>
        </figure>
      ) : null}
    </Section>
  );
}

export function PricingSection({ service }: { service: Service }) {
  if (service.tiers.length === 0) return null;

  return (
    <Section aria-labelledby="pricing-title">
      <SectionHeading
        eyebrow="Pricing"
        title="Clear starting prices"
        description="Every project gets a fixed written quote. These ranges help you plan."
        id="pricing-title"
      />
      <div className="desktop:grid-cols-3 mt-14 grid items-stretch gap-8">
        {service.tiers.map((tier, index) => {
          const last =
            index === service.tiers.length - 1 &&
            !tier.highlighted &&
            service.tiers.length === 3;
          return (
            <Card
              key={tier.name}
              tone={tier.highlighted ? "strong" : "surface"}
              radius="lg"
              hover
              className="desktop:px-9 flex flex-col gap-4.5 px-8 py-10"
            >
              {tier.highlighted ? (
                <span className="rounded-tag bg-accent absolute start-9 -top-3 px-3 py-1 text-xs font-semibold text-white">
                  Most popular
                </span>
              ) : null}
              <h3 className="text-text text-xl font-semibold">{tier.name}</h3>
              <div>
                <span className="text-meta text-dim">From </span>
                <span className="text-text text-[2.5rem] leading-none font-bold">
                  {tier.price_from}
                </span>
              </div>
              {tier.subtitle ? (
                <p className="text-ui text-muted">{tier.subtitle}</p>
              ) : null}
              <ul className="border-divider mt-2 flex grow flex-col gap-3 border-t pt-5">
                {tier.items.map((item) => (
                  <li
                    key={item}
                    className="text-ui text-text-2 flex items-start gap-3"
                  >
                    <span className="bg-success-solid mt-0.5 flex size-4.5 shrink-0 items-center justify-center rounded-full text-white">
                      <Icon name="check" className="size-3" />
                    </span>
                    {item}
                  </li>
                ))}
              </ul>
              <Button
                href={`/contact?service=${service.slug}`}
                variant={tier.highlighted ? "primary" : "outline"}
                fullWidth
                size="sm"
              >
                {last ? "Book a Call" : "Get a Quote"}
              </Button>
            </Card>
          );
        })}
      </div>
    </Section>
  );
}

export function FaqSection({ service }: { service: Service }) {
  if (service.faq.length === 0) return null;

  return (
    <Section aria-labelledby="service-faq-title">
      <div className="desktop:grid-cols-[26.25rem_minmax(0,1fr)] desktop:items-start desktop:gap-24 grid gap-10">
        <div>
          <Eyebrow>FAQ</Eyebrow>
          <h2
            id="service-faq-title"
            className="text-h2 tracking-h2 text-text mt-2.5"
          >
            Questions about {service.nav_label}
          </h2>
          <p className="text-subhead text-muted mt-4">
            More general questions are answered on the{" "}
            <Link
              href="/contact"
              className="text-link underline underline-offset-4"
            >
              contact page
            </Link>
            .
          </p>
        </div>
        <Accordion
          items={service.faq.map((item, index) => ({
            id: String(index),
            question: item.question,
            answer: item.answer,
          }))}
        />
      </div>
    </Section>
  );
}

export function ArticlesSection({ service }: { service: Service }) {
  if (service.posts.length === 0) return null;

  return (
    <Section variant="alt" aria-labelledby="service-articles-title">
      <div className="tablet:flex-row tablet:items-end tablet:justify-between flex flex-col items-start gap-5">
        <div>
          <Eyebrow>From the blog</Eyebrow>
          <h2
            id="service-articles-title"
            className="text-h2 tracking-h2 text-text mt-2.5"
          >
            Related articles
          </h2>
        </div>
        {service.related_category ? (
          <Button
            href={`/blog/category/${service.related_category.slug}`}
            variant="outline"
            className="text-ui h-11.5"
          >
            More on {service.related_category.name}{" "}
            <ArrowUpRight className="size-3.5" />
          </Button>
        ) : null}
      </div>
      <div className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-10 grid">
        {service.posts.map((post) => (
          <PostCard key={post.slug} post={post} />
        ))}
      </div>
    </Section>
  );
}

export function OtherServices({ services }: { services: ServiceSummary[] }) {
  if (services.length === 0) return null;

  return (
    <Section aria-labelledby="other-services-title">
      <SectionHeading
        eyebrow="Other services"
        title="More ways I can help"
        id="other-services-title"
      />
      <ul className="tablet:grid-cols-2 desktop:grid-cols-4 mt-12 grid gap-4">
        {services.map((item) => (
          <li key={item.slug}>
            <Link
              href={`/services/${item.slug}`}
              className="hover-card rounded-card border-border bg-surface text-ui text-text hover:border-accent flex items-center justify-between gap-3 border p-4 font-medium transition-colors"
            >
              <span className="flex items-center gap-3">
                <IconTile
                  name={item.icon}
                  tone="chip"
                  size="sm"
                  className="size-9"
                />
                {item.nav_label}
              </span>
              <ArrowUpRight className="text-dim size-4" />
            </Link>
          </li>
        ))}
      </ul>
    </Section>
  );
}
