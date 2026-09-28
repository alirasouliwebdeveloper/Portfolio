import { ProjectCard } from "@/components/project/ProjectCard";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import type { HomeContent, ProjectCard as ProjectCardData } from "@/types/api";

export function FeaturedProjects({
  content,
  projects,
}: {
  content: HomeContent["projects"];
  projects: ProjectCardData[];
}) {
  if (projects.length === 0) return null;

  return (
    <Section id="projects" variant="alt" aria-labelledby="projects-title">
      <SectionHeading
        eyebrow={content.eyebrow}
        title={content.heading}
        id="projects-title"
      />
      <div className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-12 grid">
        {projects.map((project) => (
          <ProjectCard key={project.slug} project={project} />
        ))}
      </div>
    </Section>
  );
}
