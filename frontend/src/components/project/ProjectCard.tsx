import Link from "next/link";
import { ArrowUpRight } from "@/components/ui/icons";
import { Media } from "@/components/ui/Media";
import { Tag } from "@/components/ui/Tag";
import type { ProjectCard as ProjectCardData } from "@/types/api";

export function ProjectCard({ project }: { project: ProjectCardData }) {
  return (
    <article className="rounded-card border-border bg-surface flex h-full flex-col overflow-hidden border">
      <div className="relative aspect-16/10 overflow-hidden">
        <Media
          image={project.cover}
          size="card"
          fill
          sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
        />
      </div>
      <div className="flex grow flex-col gap-2.5 p-6.5">
        <h3 className="text-card-title text-text font-semibold">
          <Link href={`/projects/${project.slug}`} className="hover:text-white">
            {project.title}
          </Link>
        </h3>
        <p className="text-ui text-muted leading-relaxed">{project.summary}</p>
        <div className="mt-auto flex items-center justify-between gap-3 pt-2.5">
          <div className="flex gap-1.5">
            {project.tags.map((tag) => (
              <Tag key={tag} size="sm">
                {tag}
              </Tag>
            ))}
          </div>
          <Link
            href={`/projects/${project.slug}`}
            className="text-link flex items-center gap-2 text-sm whitespace-nowrap"
          >
            View Project <ArrowUpRight className="size-3.5" />
          </Link>
        </div>
      </div>
    </article>
  );
}
