import { IconTile } from "@/components/ui/IconTile";
import type { Project } from "@/types/api";

export function ProjectMeta({ project }: { project: Project }) {
  const items = [
    { icon: "user" as const, label: "Client", value: project.client },
    { icon: "code" as const, label: "My role", value: project.role },
    { icon: "clock" as const, label: "Timeline", value: project.timeline },
    {
      icon: "calendar" as const,
      label: "Year",
      value: project.year ? String(project.year) : null,
    },
  ].filter((item) => item.value);

  if (items.length === 0) return null;

  return (
    <dl className="desktop:grid-cols-4 desktop:gap-6 grid grid-cols-2 gap-4">
      {items.map((item) => (
        <div
          key={item.label}
          className="rounded-card border-border bg-surface desktop:px-6 desktop:py-5.5 flex items-center gap-4 border px-5 py-5"
        >
          <IconTile
            name={item.icon}
            tone="chip"
            size="sm"
            className="size-12"
          />
          <div>
            <dt className="text-caption text-dim">{item.label}</dt>
            <dd className="text-text mt-1 text-base font-medium">
              {item.value}
            </dd>
          </div>
        </div>
      ))}
    </dl>
  );
}
