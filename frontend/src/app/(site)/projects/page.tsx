import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { ProjectBrowser } from "@/components/project/ProjectBrowser";
import { CtaBand } from "@/components/sections/CtaBand";
import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { ArrowUpRight } from "@/components/ui/icons";
import { JsonLd } from "@/components/ui/JsonLd";
import { Section } from "@/components/ui/Section";
import { getProjects, getProjectsPage, getSettings } from "@/lib/api";
import { breadcrumbJsonLd } from "@/lib/jsonld";
import { pageMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const [page, settings] = await Promise.all([
    getProjectsPage(),
    getSettings(),
  ]);
  return page ? pageMetadata({ page, settings, path: "/projects" }) : {};
}

export default async function ProjectsPage() {
  const [page, projects] = await Promise.all([
    getProjectsPage(),
    getProjects(),
  ]);
  if (!page) notFound();

  return (
    <>
      <JsonLd
        data={[
          breadcrumbJsonLd([
            { name: "Home", path: "/" },
            { name: "Projects", path: "/projects" },
          ]),
        ]}
      />

      <section
        aria-labelledby="projects-title"
        className="border-line-soft bg-bg border-b pt-10 pb-12"
      >
        <Container>
          <Breadcrumbs
            items={[{ label: "Home", href: "/" }, { label: "Projects" }]}
          />
          <div className="mt-8 max-w-200">
            <Eyebrow>{page.content.eyebrow}</Eyebrow>
            <h1
              id="projects-title"
              className="text-page-h1 tracking-h1 text-text mt-3 leading-[1.15]"
            >
              {page.title}
            </h1>
            {page.content.description ? (
              <p className="text-lead text-muted mt-4">
                {page.content.description}
              </p>
            ) : null}
          </div>
        </Container>
      </section>

      <Section aria-labelledby="project-list-title">
        <h2 id="project-list-title" className="sr-only">
          All projects
        </h2>
        {projects.length > 0 ? (
          <ProjectBrowser projects={projects} />
        ) : (
          <p className="text-lead text-muted">
            New case studies are on the way.
          </p>
        )}
      </Section>

      <CtaBand
        heading="Have a project in mind?"
        text="Tell me what you need — you'll get a clear quote within two working days."
      >
        <Button href="/contact">
          Get a Free Quote <ArrowUpRight className="size-3.5" />
        </Button>
      </CtaBand>
    </>
  );
}
