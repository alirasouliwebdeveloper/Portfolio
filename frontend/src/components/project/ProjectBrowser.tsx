"use client";

import { useEffect, useMemo, useState } from "react";
import { FilterChip } from "@/components/ui/Chip";
import type { ProjectCard as ProjectCardData } from "@/types/api";
import { ProjectCard } from "./ProjectCard";

/** "Next.js" → "next-js": the value kept in `?tech=` so a filtered list can be shared. */
const techKey = (name: string) =>
  name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");

/**
 * Technology filter for /projects. Every card is rendered on the server and is in the HTML;
 * filtering only hides cards in the browser, so crawlers always see the full list.
 */
export function ProjectBrowser({ projects }: { projects: ProjectCardData[] }) {
  const technologies = useMemo(() => {
    const counts = new Map<string, { name: string; count: number }>();
    for (const project of projects) {
      for (const name of new Set(project.stack.map((item) => item.trim()))) {
        if (!name) continue;
        const key = techKey(name);
        const entry = counts.get(key) ?? { name, count: 0 };
        entry.count += 1;
        counts.set(key, entry);
      }
    }
    // Most used first.
    return [...counts.entries()]
      .sort(
        (a, b) => b[1].count - a[1].count || a[1].name.localeCompare(b[1].name),
      )
      .map(([key, value]) => ({ key, ...value }));
  }, [projects]);

  const [active, setActive] = useState<string | null>(null);

  // Read the shared filter after hydration; the static HTML always shows every project.
  useEffect(() => {
    const tech = new URLSearchParams(window.location.search).get("tech");
    if (tech && technologies.some((item) => item.key === tech)) {
      // eslint-disable-next-line react-hooks/set-state-in-effect -- sync from the URL once on mount
      setActive(tech);
    }
  }, [technologies]);

  const select = (key: string | null) => {
    setActive(key);
    const url = new URL(window.location.href);
    if (key) url.searchParams.set("tech", key);
    else url.searchParams.delete("tech");
    window.history.replaceState(null, "", url);
  };

  const visible = active
    ? projects.filter((project) =>
        project.stack.some((name) => techKey(name) === active),
      )
    : projects;

  return (
    <>
      {technologies.length > 0 ? (
        <div role="group" aria-label="Filter projects by technology">
          <ul className="flex flex-wrap gap-2.5">
            <li>
              <FilterChip
                active={active === null}
                count={projects.length}
                onClick={() => select(null)}
              >
                All
              </FilterChip>
            </li>
            {technologies.map((tech) => (
              <li key={tech.key}>
                <FilterChip
                  active={active === tech.key}
                  count={tech.count}
                  onClick={() => select(active === tech.key ? null : tech.key)}
                >
                  {tech.name}
                </FilterChip>
              </li>
            ))}
          </ul>
        </div>
      ) : null}

      <p className="sr-only" aria-live="polite">
        {visible.length} {visible.length === 1 ? "project" : "projects"} shown
      </p>

      <ul className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-10 grid">
        {projects.map((project) => (
          <li
            key={project.slug}
            hidden={!visible.includes(project)}
            className="h-full"
          >
            <ProjectCard project={project} />
          </li>
        ))}
      </ul>
    </>
  );
}
