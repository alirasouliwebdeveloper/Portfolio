import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { Accordion } from "@/components/ui/Accordion";
import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { FilterChip } from "@/components/ui/Chip";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { Input } from "@/components/ui/Input";
import { Pagination } from "@/components/ui/Pagination";
import { RadioPillGroup } from "@/components/ui/RadioPill";
import { SearchInput } from "@/components/ui/SearchInput";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Select } from "@/components/ui/Select";
import { Tag } from "@/components/ui/Tag";
import { Textarea } from "@/components/ui/Textarea";
import { IconTile } from "@/components/ui/IconTile";
import { ArrowRight, ArrowUpRight, iconNames } from "@/components/ui/icons";

export const metadata: Metadata = {
  title: "Styleguide",
  robots: { index: false, follow: false },
};

const swatches = [
  ["bg", "bg-bg"],
  ["bg-alt", "bg-bg-alt"],
  ["bg-footer", "bg-bg-footer"],
  ["surface", "bg-surface"],
  ["surface-input", "bg-surface-input"],
  ["surface-strong", "bg-surface-strong"],
  ["border", "bg-border"],
  ["border-input", "bg-border-input"],
  ["divider", "bg-divider"],
  ["line", "bg-line"],
  ["chip", "bg-chip"],
  ["chip-text", "bg-chip-text"],
  ["tile", "bg-tile"],
  ["accent", "bg-accent"],
  ["accent-2", "bg-accent-2"],
  ["accent-soft", "bg-accent-soft"],
  ["eyebrow", "bg-eyebrow"],
  ["link", "bg-link"],
  ["icon-soft", "bg-icon-soft"],
  ["text", "bg-text"],
  ["text-2", "bg-text-2"],
  ["muted", "bg-muted"],
  ["dim", "bg-dim"],
  ["success", "bg-success"],
  ["success-solid", "bg-success-solid"],
  ["danger", "bg-danger"],
  ["danger-border", "bg-danger-border"],
  ["star", "bg-star"],
] as const;

const faq = [
  {
    id: "one",
    question: "How long does a typical project take?",
    answer:
      "Most small business sites take three to six weeks from kickoff to launch.",
  },
  {
    id: "two",
    question: "Do you work with clients outside Oman?",
    answer:
      "Yes. I work remotely with clients in different countries and time zones.",
  },
  {
    id: "three",
    question: "Can I edit the content myself?",
    answer: "Everything is editable from an admin panel, no code required.",
  },
];

function Block({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-5">
      <h3 className="text-card-title-lg text-text">{title}</h3>
      {children}
    </div>
  );
}

export default function StyleguidePage() {
  if (process.env.NODE_ENV === "production") notFound();

  return (
    <main>
      <Section bordered>
        <Breadcrumbs
          items={[{ label: "Home", href: "/" }, { label: "Styleguide" }]}
        />
        <div className="mt-8">
          <Eyebrow>Design system</Eyebrow>
          <h1 className="text-page-h1 tracking-h1 text-text mt-3">
            Styleguide
          </h1>
          <p className="text-lead text-muted mt-4 max-w-160">
            Every UI component built from the tokens in globals.css. Resize the
            window to check 390 / 834 / 1920.
          </p>
        </div>
      </Section>

      <Section variant="alt" containerClassName="flex flex-col gap-16">
        <Block title="Colors">
          <ul className="tablet:grid-cols-4 desktop:grid-cols-7 grid grid-cols-2 gap-4">
            {swatches.map(([name, className]) => (
              <li key={name} className="flex flex-col gap-2">
                <span
                  className={`rounded-control border-border h-16 border ${className}`}
                />
                <span className="text-caption text-text-2">{name}</span>
              </li>
            ))}
          </ul>
        </Block>

        <Block title="Typography">
          <div className="flex flex-col gap-4">
            <p className="text-hero-h1 tracking-h1 font-semibold">
              Hero H1 — Hi, I’m Ali
            </p>
            <p className="text-hero-sub tracking-h1 font-semibold">
              Hero sub — I build things for the web.
            </p>
            <p className="text-page-h1 tracking-h1 font-semibold">
              Page H1 — Articles &amp; Notes
            </p>
            <p className="text-h2 tracking-h2 font-semibold">
              Section H2 — What I Build With
            </p>
            <p className="text-card-title-lg font-semibold">
              Card title large — Building an admin panel
            </p>
            <p className="text-card-title font-semibold">
              Card title — E-Commerce Platform
            </p>
            <p className="text-lead text-muted max-w-160">
              Lead — A full-stack developer building fast websites, online
              stores and web apps.
            </p>
            <p className="text-body text-muted max-w-160">
              Body — Smart-home store with a custom admin panel and fast product
              pages.
            </p>
            <p className="text-article text-text-2 max-w-160">
              Article — The long-form body copy used inside blog posts.
            </p>
            <p className="text-meta text-dim">
              Meta — Sep 22, 2026 · 9 min read
            </p>
            <code className="text-code text-sm">
              const developer = &#123; name: “Ali” &#125;;
            </code>
          </div>
        </Block>

        <Block title="Buttons">
          <div className="flex flex-wrap items-center gap-4">
            <Button href="/">
              View My Work <ArrowUpRight className="size-4" />
            </Button>
            <Button href="/" variant="outline">
              Download CV
            </Button>
            <Button size="sm" variant="outline">
              Learn More About Me
            </Button>
            <Button size="nav" href="/contact">
              Hire Me <ArrowRight className="size-4" />
            </Button>
            <Button disabled>Disabled</Button>
          </div>
          <Button fullWidth className="tablet:hidden">
            Full width on mobile
          </Button>
        </Block>

        <Block title="Eyebrow, section heading, tags and chips">
          <div className="flex flex-wrap items-center gap-4">
            <Eyebrow variant="chip">I’m a web developer</Eyebrow>
            <Eyebrow>My stack</Eyebrow>
          </div>
          <SectionHeading
            eyebrow="Featured projects"
            title="Some of My Recent Work"
            description="The tools I use every day, grouped by the part of the product they power."
          />
          <div className="flex flex-wrap items-center gap-2">
            <Tag>Laravel</Tag>
            <Tag>Next.js</Tag>
            <Tag size="sm">Docker</Tag>
            <Tag size="sm">n8n</Tag>
          </div>
          <div className="flex flex-wrap gap-2.5">
            <FilterChip href="/styleguide" active count={43}>
              All
            </FilterChip>
            <FilterChip href="/styleguide" count={12}>
              Laravel
            </FilterChip>
            <FilterChip href="/styleguide" count={9}>
              Next.js
            </FilterChip>
          </div>
        </Block>

        <Block title="Icon tiles and icon map">
          <div className="flex flex-wrap items-center gap-4">
            <IconTile name="db" />
            <IconTile name="code" tone="chip" />
            <IconTile name="trophy" tone="accent" />
            <IconTile name="mail" size="sm" />
            <IconTile name="rocket" size="lg" />
          </div>
          <ul className="tablet:grid-cols-6 desktop:grid-cols-9 grid grid-cols-3 gap-3">
            {iconNames.map((name) => (
              <li
                key={name}
                className="text-caption text-dim flex flex-col items-center gap-2"
              >
                <IconTile name={name} size="sm" />
                {name}
              </li>
            ))}
          </ul>
        </Block>

        <Block title="Cards">
          <div className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 grid">
            <Card className="flex flex-col gap-4">
              <div className="flex items-center gap-4">
                <IconTile name="db" />
                <h4 className="text-card-title-lg">Backend</h4>
              </div>
              <p className="text-body text-muted">
                APIs, admin panels and business logic for stores and internal
                tools.
              </p>
              <div className="flex flex-wrap gap-2">
                <Tag>Laravel</Tag>
                <Tag>PHP</Tag>
              </div>
            </Card>
            <Card tone="strong">
              <h4 className="text-card-title-lg">Highlighted (strong)</h4>
              <p className="text-body text-muted mt-2">
                Used for the open FAQ item and the popular pricing tier.
              </p>
            </Card>
            <Card radius="lg" padded={false} className="overflow-hidden">
              <div className="bg-primary-gradient h-24" />
              <p className="p-card text-body text-muted">
                Large radius, no padding (image cards).
              </p>
            </Card>
          </div>
        </Block>

        <Block title="Breadcrumbs and pagination">
          <Breadcrumbs
            items={[
              { label: "Home", href: "/" },
              { label: "Blog", href: "/blog" },
              { label: "Search" },
            ]}
          />
          <Pagination
            current={1}
            total={8}
            href={(page) => `/styleguide?page=${page}`}
          />
          <Pagination
            current={4}
            total={8}
            href={(page) => `/styleguide?page=${page}`}
          />
          <Pagination
            current={8}
            total={8}
            href={(page) => `/styleguide?page=${page}`}
          />
        </Block>

        <Block title="Search">
          <SearchInput
            visibleLabel
            placeholder="e.g. Filament, Docker, SEO"
            className="max-w-105"
          />
          <SearchInput
            withButton
            defaultValue="laravel"
            clearHref="/styleguide"
            className="max-w-190"
          />
        </Block>

        <Block title="Form controls">
          <div className="tablet:grid-cols-2 grid gap-5">
            <Input
              label="Your name"
              defaultValue="Sara Ahmadi"
              autoComplete="name"
            />
            <Input label="Email" type="email" placeholder="you@example.com" />
            <Input label="Company" optional />
            <Input
              label="Email"
              type="email"
              defaultValue="not-an-email"
              error="Enter a valid email address."
            />
            <Select
              label="Budget"
              options={[
                "$1,500 – $4,000",
                "$4,000 – $8,000",
                "$8,000+",
                "Not sure yet",
              ]}
            />
            <Select
              label="Timeline"
              options={["As soon as possible", "Within 1–3 months"]}
              error="Choose a timeline."
            />
          </div>
          <RadioPillGroup
            legend="What do you need?"
            name="need"
            defaultValue="Online store"
            options={[
              "Website or web app",
              "Online store",
              "API or backend",
              "Automation",
              "Something else",
            ]}
          />
          <Textarea
            label="Project details"
            defaultValue="We sell smart locks and need a faster store."
          />
          <Textarea
            label="Project details"
            error="Please write at least 10 characters."
          />
        </Block>

        <Block title="Accordion">
          <Accordion items={faq} className="max-w-190" />
        </Block>
      </Section>
    </main>
  );
}
