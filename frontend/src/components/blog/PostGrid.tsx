import type { ReactNode } from "react";
import { Pagination } from "@/components/ui/Pagination";
import { Section } from "@/components/ui/Section";
import type { PaginationMeta, PostCard as PostCardData } from "@/types/api";
import { PostCard } from "./PostCard";

type PostGridProps = {
  /** Section heading with "Showing x–y of total"; omit when a `toolbar` replaces it. */
  heading?: string;
  toolbar?: ReactNode;
  posts: PostCardData[];
  meta: PaginationMeta;
  href: (page: number) => string;
  query?: string;
};

/** The 6-per-page grid with pagination. */
export function PostGrid({
  heading,
  toolbar,
  posts,
  meta,
  href,
  query,
}: PostGridProps) {
  return (
    <Section
      aria-labelledby={heading ? "latest-title" : undefined}
      className="tablet:pb-20 desktop:pt-14 desktop:pb-24 pt-12 pb-16"
    >
      {toolbar ? (
        <div className="tablet:flex-row tablet:items-center tablet:justify-between flex flex-col gap-4">
          {toolbar}
        </div>
      ) : (
        <div className="flex flex-wrap items-baseline gap-x-5 gap-y-1">
          <h2 id="latest-title" className="text-h2 tracking-h2 text-text">
            {heading}
          </h2>
          {meta.total > 0 ? (
            <span className="text-meta text-dim">
              Showing {meta.from}–{meta.to} of {meta.total}
            </span>
          ) : null}
        </div>
      )}

      {posts.length > 0 ? (
        <ul className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-8 grid">
          {posts.map((post) => (
            <li key={post.slug}>
              <PostCard post={post} query={query} />
            </li>
          ))}
        </ul>
      ) : (
        <p className="rounded-card border-border bg-surface text-body text-muted mt-10 border p-8">
          No articles yet — check back soon.
        </p>
      )}

      <Pagination
        current={meta.current_page}
        total={meta.last_page}
        href={href}
        className="mt-16"
      />
    </Section>
  );
}
