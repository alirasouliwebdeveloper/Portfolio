import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ProjectMeta } from "@/components/project/ProjectMeta";
import { QuoteBlock } from "@/components/project/QuoteBlock";
import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { JsonLd } from "@/components/ui/JsonLd";
import { Media } from "@/components/ui/Media";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Tag } from "@/components/ui/Tag";
import { ArrowUpRight } from "@/components/ui/icons";
import { getProject, getSettings, getSitemap } from "@/lib/api";
import { breadcrumbJsonLd, creativeWorkJsonLd } from "@/lib/jsonld";
import { buildMetadata } from "@/lib/seo";
import { redirectIfMoved } from "@/lib/redirects";

export const dynamicParams = true;

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  return sitemap.projects.map((project) => ({ slug: project.slug }));
}

export async function generateMetadata({
  params,
}: PageProps<"/projects/[slug]">): Promise<Metadata> {
  const { slug } = await params;
  const [project, settings] = await Promise.all([
    getProject(slug),
    getSettings(),
  ]);
  if (!project) return {};

  return buildMetadata({
    title: project.title,
    seo: project.seo,
    settings,
    path: `/projects/${project.slug}`,
    fallbackImage: project.cover,
  });
}

export default async function ProjectPage({
  params,
}: PageProps<"/projects/[slug]">) {
  const { slug } = await params;
  const project = await getProject(slug);
  if (!project) {
    await redirectIfMoved(`/projects/${slug}`);
    notFound();
  }

  const overview = [
    {
      icon: "alert" as const,
      title: "The challenge",
      html: project.challenge_html,
    },
    {
      icon: "bulb" as const,
      title: "The solution",
      html: project.solution_html,
    },
    { icon: "trend" as const, title: "The result", html: project.result_html },
  ].filter((item) => item.html);

  return (
    <>
      <JsonLd
        data={[
          creativeWorkJsonLd(project),
          breadcrumbJsonLd([
            { name: "Home", path: "/" },
            { name: "Projects", path: "/#projects" },
            { name: project.title, path: `/projects/${project.slug}` },
          ]),
        ]}
      />

      <Section
        bordered
        flush
        aria-labelledby="project-title"
        className="pt-10 pb-16"
      >
        <Breadcrumbs
          items={[
            { label: "Home", href: "/" },
            { label: "Projects", href: "/#projects" },
            { label: project.title },
          ]}
        />

        <div className="desktop:flex-row desktop:items-end desktop:justify-between desktop:gap-20 mt-12 flex flex-col gap-8">
          <div className="max-w-215">
            <Eyebrow>Case study</Eyebrow>
            <h1
              id="project-title"
              className="text-case-h1 tracking-h1 text-text mt-3.5"
            >
              {project.title}
            </h1>
            {project.lead_html ? (
              <RichContent
                html={project.lead_html}
                className="text-lead mt-5"
              />
            ) : (
              <p className="text-lead text-muted mt-5">{project.summary}</p>
            )}
          </div>
          <div className="tablet:flex-row flex shrink-0 flex-col gap-3.5">
            {project.live_url ? (
              <Button
                href={project.live_url}
                target="_blank"
                fullWidth
                className="tablet:w-auto"
              >
                Visit Live Site <ArrowUpRight className="size-3.5" />
              </Button>
            ) : null}
            {project.service ? (
              <Button
                href={`/services/${project.service.slug}`}
                variant="outline"
                fullWidth
                className="tablet:w-auto"
              >
                {project.service.title}
              </Button>
            ) : null}
          </div>
        </div>

        <div className="mt-12">
          <ProjectMeta project={project} />
        </div>

        {project.cover ? (
          <div className="rounded-card-lg border-border bg-surface relative mt-12 aspect-16/10 overflow-hidden border">
            <Media
              image={project.cover}
              size="lg"
              fill
              sheen
              priority
              sizes="(min-width: 1520px) 1440px, 100vw"
            />
          </div>
        ) : null}
      </Section>

      {overview.length > 0 ? (
        <Section variant="alt" aria-labelledby="overview-title">
          <SectionHeading
            eyebrow="Overview"
            title="From problem to launch"
            id="overview-title"
          />
          <div className="gap-grid desktop:grid-cols-3 mt-12 grid">
            {overview.map((item) => (
              <Card
                key={item.title}
                hover
                className="desktop:p-8.5 flex flex-col gap-4"
              >
                <div className="flex items-center gap-4">
                  <IconTile name={item.icon} tone="tile" />
                  <h3 className="text-card-title-lg text-text font-semibold">
                    {item.title}
                  </h3>
                </div>
                <RichContent html={item.html ?? ""} />
              </Card>
            ))}
          </div>
        </Section>
      ) : null}

      {project.features.length > 0 ? (
        <Section aria-labelledby="features-title">
          <SectionHeading
            eyebrow="What I built"
            title="Key features"
            id="features-title"
          />
          <div className="tablet:grid-cols-2 tablet:gap-6 desktop:grid-cols-3 mt-12 grid gap-4">
            {project.features.map((feature) => (
              <Card
                key={feature.title}
                hover
                className="desktop:p-6.5 flex items-center gap-4.5 p-5"
              >
                <IconTile
                  name={feature.icon}
                  tone="chip"
                  className="size-15 shrink-0"
                />
                <div>
                  <h3 className="text-text text-[1.0625rem] font-semibold">
                    {feature.title}
                  </h3>
                  <p className="text-ui text-muted mt-1.5 leading-[1.55]">
                    {feature.text}
                  </p>
                </div>
              </Card>
            ))}
          </div>
        </Section>
      ) : null}

      {project.screens.length > 0 ? (
        <Section variant="alt" aria-labelledby="screens-title">
          <SectionHeading
            eyebrow="Screens"
            title="A closer look"
            id="screens-title"
          />
          <ul className="tablet:grid-cols-2 mt-12 grid gap-8">
            {project.screens.map((screen, index) => (
              <li key={`${screen.caption}-${index}`}>
                <figure className="flex flex-col gap-3">
                  <div className="hover-card rounded-card-lg border-border bg-surface relative aspect-16/10 overflow-hidden border">
                    <Media
                      image={screen.image}
                      size="md"
                      fill
                      sheen
                      sizes="(min-width: 768px) 50vw, 100vw"
                    />
                  </div>
                  {screen.caption ? (
                    <figcaption className="text-meta text-dim">
                      {screen.caption}
                    </figcaption>
                  ) : null}
                </figure>
              </li>
            ))}
          </ul>
        </Section>
      ) : null}

      {project.stack.length > 0 ||
      project.metrics.length > 0 ||
      project.testimonial ? (
        <Section aria-labelledby="built-with-title">
          <div className="desktop:flex-row desktop:items-center desktop:justify-between desktop:gap-12 flex flex-col gap-5">
            <div>
              <Eyebrow>Tech stack</Eyebrow>
              <h2
                id="built-with-title"
                className="tablet:text-[1.875rem] desktop:text-[2rem] mt-2.5 text-[1.625rem] font-semibold"
              >
                Built with
              </h2>
            </div>
            <ul className="desktop:justify-end flex flex-wrap gap-2.5">
              {project.stack.map((tech) => (
                <li key={tech}>
                  <Tag size="lg">{tech}</Tag>
                </li>
              ))}
            </ul>
          </div>

          {project.metrics.length > 0 ? (
            <ul className="tablet:grid-cols-3 tablet:gap-6 desktop:mt-16 desktop:gap-8 mt-12 grid gap-4">
              {project.metrics.map((metric) => (
                <Card
                  key={metric.label}
                  as="li"
                  hover
                  className="desktop:p-8.5 p-8 text-center"
                >
                  <div className="text-text desktop:text-[2.875rem] text-[2.25rem] leading-tight font-semibold">
                    {metric.value}
                  </div>
                  <div className="text-ui text-muted mt-2">{metric.label}</div>
                </Card>
              ))}
            </ul>
          ) : null}

          {project.testimonial ? (
            <div className="desktop:mt-16 mt-12">
              <QuoteBlock testimonial={project.testimonial} />
            </div>
          ) : null}
        </Section>
      ) : null}

      {project.next ? (
        <Section
          variant="alt"
          aria-labelledby="next-title"
          className="desktop:py-20 py-14"
        >
          <Link
            href={`/projects/${project.next.slug}`}
            className="hover-card rounded-card border-border bg-surface desktop:grid-cols-2 grid overflow-hidden border"
          >
            <div className="desktop:px-16 desktop:py-14 flex flex-col justify-center gap-3 p-8">
              <span className="text-meta text-dim">Next project</span>
              <h2
                id="next-title"
                className="text-text desktop:text-[2.375rem] text-[1.875rem] leading-tight font-semibold"
              >
                {project.next.title}
              </h2>
              <p className="text-body text-muted">{project.next.summary}</p>
              <span className="text-ui text-link mt-2 flex items-center gap-2">
                View Case Study <ArrowUpRight className="size-3.5" />
              </span>
            </div>
            <div className="relative aspect-16/10">
              <Media
                image={project.next.cover}
                size="card"
                fill
                sheen
                sizes="(min-width: 1280px) 50vw, 100vw"
              />
            </div>
          </Link>
        </Section>
      ) : null}
    </>
  );
}
