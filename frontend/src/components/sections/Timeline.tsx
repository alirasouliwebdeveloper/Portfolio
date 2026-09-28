import { cn } from "@/lib/cn";
import type { Experience } from "@/types/api";

/** Experience timeline: newest first, the first dot uses the accent color. */
export function Timeline({ items }: { items: Experience[] }) {
  return (
    <ol className="mx-auto max-w-190">
      {items.map((item, index) => {
        const period = `${item.start_year} — ${item.end_year ?? "Present"}`;
        return (
          <li
            key={`${item.role}-${item.start_year}`}
            className="tablet:grid-cols-[8.5rem_1.75rem_1fr] tablet:gap-x-5 grid grid-cols-[1.75rem_1fr] gap-x-4"
          >
            <div className="text-caption text-dim tablet:col-start-1 tablet:pt-1 tablet:text-end col-start-2 row-start-1">
              {period}
            </div>
            <div className="tablet:col-start-2 col-start-1 row-span-2 row-start-1 flex flex-col items-center">
              <span
                className={cn(
                  "mt-1.5 size-3 shrink-0 rounded-full",
                  index === 0 ? "bg-accent" : "bg-border-input",
                )}
              />
              {index < items.length - 1 ? (
                <span className="bg-divider w-px grow" />
              ) : null}
            </div>
            <div className="tablet:col-start-3 tablet:row-start-1 col-start-2 row-start-2 pb-10">
              <h3 className="text-card-title text-text font-semibold">
                {item.role} <span className="text-dim font-normal">·</span>{" "}
                <span className="text-link">{item.company}</span>
              </h3>
              {item.description ? (
                <p className="text-ui text-muted mt-2 leading-relaxed">
                  {item.description}
                </p>
              ) : null}
            </div>
          </li>
        );
      })}
    </ol>
  );
}
