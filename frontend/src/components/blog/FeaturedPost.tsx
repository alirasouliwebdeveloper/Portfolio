import Link from "next/link";
import { Container } from "@/components/ui/Container";
import { Media } from "@/components/ui/Media";
import { ArrowUpRight } from "@/components/ui/icons";
import { formatPostMeta } from "@/lib/format";
import type { PostCard } from "@/types/api";

export function FeaturedPost({ post }: { post: PostCard }) {
  return (
    <section aria-labelledby="featured-title" className="bg-bg pt-14">
      <Container>
        <h2 id="featured-title" className="sr-only">
          Featured
        </h2>
        <article className="hover-card rounded-card-lg border-border bg-surface desktop:grid-cols-[1.2fr_1fr] grid overflow-hidden border">
          <div className="relative aspect-16/10">
            <Media
              image={post.cover}
              size="lg"
              fill
              sheen
              priority
              sizes="(min-width: 1280px) 55vw, 100vw"
            />
          </div>
          <div className="desktop:p-14 flex flex-col justify-center gap-4 p-8">
            <div className="flex items-center gap-3">
              <span className="rounded-tag bg-accent px-2.75 py-1.5 text-xs font-semibold text-white">
                Featured
              </span>
              <Link
                href={`/blog/category/${post.category.slug}`}
                className="rounded-tag bg-chip text-chip-text px-2.75 py-1.5 text-xs font-medium"
              >
                {post.category.name}
              </Link>
            </div>
            <div className="text-caption text-dim">
              {formatPostMeta(post.published_at, post.reading_time)}
            </div>
            <h2 className="tracking-h2 text-text desktop:text-[2.125rem] text-[1.75rem] leading-tight font-semibold">
              <Link
                href={`/blog/${post.slug}`}
                className="transition-colors hover:text-white"
              >
                {post.title}
              </Link>
            </h2>
            <p className="text-subhead text-muted">{post.excerpt}</p>
            <Link
              href={`/blog/${post.slug}`}
              className="text-ui text-link mt-2 flex items-center gap-2"
            >
              Read Article <ArrowUpRight className="size-3.5" />
            </Link>
          </div>
        </article>
      </Container>
    </section>
  );
}
