import { Breadcrumbs, type BreadcrumbItem } from "@/components/ui/Breadcrumbs";
import { FilterChip } from "@/components/ui/Chip";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { SearchInput } from "@/components/ui/SearchInput";
import type { ReactNode } from "react";
import { Rss } from "lucide-react";

export type BlogChip = {
  label: string;
  href: string;
  count?: number;
  active?: boolean;
};

type BlogHeaderProps = {
  breadcrumbs: BreadcrumbItem[];
  eyebrow: string;
  title: ReactNode;
  description?: string;
  chips?: BlogChip[];
  /** Show the article search box (hidden on the search page, which has its own). */
  search?: boolean;
  compact?: boolean;
  children?: ReactNode;
};

/** Header shared by every blog list route: breadcrumb, title, search and category chips. */
export function BlogHeader({
  breadcrumbs,
  eyebrow,
  title,
  description,
  chips,
  search = true,
  compact,
  children,
}: BlogHeaderProps) {
  return (
    <section
      aria-labelledby="blog-title"
      className="border-line-soft bg-bg border-b pt-10 pb-12"
    >
      <Container>
        <Breadcrumbs items={breadcrumbs} />
        <div className="desktop:flex-row desktop:items-end desktop:justify-between desktop:gap-16 mt-8 flex flex-col gap-8">
          <div className="max-w-200">
            <div className="flex items-center justify-between gap-4">
              <Eyebrow>{eyebrow}</Eyebrow>
              <a
                href="/blog/rss.xml"
                className="text-dim hover:text-text hover:border-accent-border rounded-control flex items-center gap-1.5 border border-transparent px-2 py-1 text-xs font-medium transition-[color,border-color,scale] duration-300 ease-out hover:scale-105"
                aria-label="RSS feed"
              >
                <Rss
                  className="size-3.5"
                  strokeWidth={1.8}
                  aria-hidden="true"
                />
                RSS
              </a>
            </div>
            <h1
              id="blog-title"
              className="text-page-h1 tracking-h1 text-text mt-3 leading-[1.15]"
            >
              {title}
            </h1>
            {description && !compact ? (
              <p className="text-lead text-muted mt-4">{description}</p>
            ) : null}
          </div>
          {search ? (
            <SearchInput
              visibleLabel
              placeholder="e.g. Filament, Docker, SEO"
              className="desktop:w-105 desktop:shrink-0 w-full"
            />
          ) : null}
        </div>

        {children ? <div className="mt-7">{children}</div> : null}

        {chips && chips.length > 0 ? (
          <ul className="mt-8 flex flex-wrap gap-2.5">
            {chips.map((chip) => (
              <li key={chip.label}>
                <FilterChip
                  href={chip.href}
                  count={chip.count}
                  active={chip.active}
                >
                  {chip.label}
                </FilterChip>
              </li>
            ))}
          </ul>
        ) : null}
      </Container>
    </section>
  );
}
