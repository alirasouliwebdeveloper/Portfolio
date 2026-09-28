import Link from "next/link";
import { ArrowUpRight } from "@/components/ui/icons";
import { Highlighted } from "@/components/ui/Highlighted";
import { Media } from "@/components/ui/Media";
import { formatPostMeta } from "@/lib/format";
import type { PostCard as PostCardData } from "@/types/api";

export function PostCard({
  post,
  priority,
  query,
}: {
  post: PostCardData;
  priority?: boolean;
  query?: string;
}) {
  return (
    <article className="rounded-card border-border bg-surface flex h-full flex-col overflow-hidden border">
      <div className="relative aspect-16/10 overflow-hidden">
        <Media
          image={post.cover}
          size="card"
          fill
          priority={priority}
          sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
        />
        <Link
          href={`/blog/category/${post.category.slug}`}
          className="rounded-tag bg-bg/80 text-chip-text absolute start-4 top-4 px-2.75 py-1.5 text-[0.78rem] font-medium backdrop-blur-sm"
        >
          {post.category.name}
        </Link>
      </div>
      <div className="flex grow flex-col gap-2.5 p-6">
        <div className="text-caption text-dim">
          {formatPostMeta(post.published_at, post.reading_time)}
        </div>
        <h3 className="text-card-title text-text font-semibold">
          <Link href={`/blog/${post.slug}`} className="hover:text-white">
            {query ? (
              <Highlighted text={post.title} query={query} />
            ) : (
              post.title
            )}
          </Link>
        </h3>
        <p className="text-ui text-muted leading-relaxed">
          {query ? (
            <Highlighted text={post.excerpt} query={query} />
          ) : (
            post.excerpt
          )}
        </p>
        <Link
          href={`/blog/${post.slug}`}
          className="text-link mt-auto flex items-center gap-2 pt-2 text-sm"
        >
          Read Article <ArrowUpRight className="size-3.5" />
        </Link>
      </div>
    </article>
  );
}
