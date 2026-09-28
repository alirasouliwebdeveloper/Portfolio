import { PostCard } from "@/components/blog/PostCard";
import { Button } from "@/components/ui/Button";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { Section } from "@/components/ui/Section";
import { ArrowUpRight } from "@/components/ui/icons";
import type { HomeContent, PostCard as PostCardData } from "@/types/api";

export function LatestArticles({
  content,
  posts,
}: {
  content: HomeContent["blog"];
  posts: PostCardData[];
}) {
  if (posts.length === 0) return null;

  return (
    <Section variant="alt" aria-labelledby="blog-title">
      <div className="tablet:flex-row tablet:items-end tablet:justify-between flex flex-col items-start gap-5">
        <div>
          <Eyebrow>{content.eyebrow}</Eyebrow>
          <h2 id="blog-title" className="text-h2 tracking-h2 text-text mt-2.5">
            {content.heading}
          </h2>
        </div>
        <Button
          href="/blog"
          variant="outline"
          className="text-ui desktop:h-11.5 h-11.5"
        >
          View All Articles <ArrowUpRight className="size-3.5" />
        </Button>
      </div>

      <div className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-10 grid">
        {posts.map((post, index) => (
          <div
            key={post.slug}
            className={
              index === 2 ? "tablet:max-desktop:hidden h-full" : "h-full"
            }
          >
            <PostCard post={post} />
          </div>
        ))}
      </div>
    </Section>
  );
}
