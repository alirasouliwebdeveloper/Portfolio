import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { PostCard } from "@/components/blog/PostCard";
import { ShareButtons } from "@/components/blog/ShareButtons";
import { TableOfContents } from "@/components/blog/TableOfContents";
import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { JsonLd } from "@/components/ui/JsonLd";
import { Media } from "@/components/ui/Media";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import { Tag } from "@/components/ui/Tag";
import { ArrowUpRight, ChevronLeft, ChevronRight } from "@/components/ui/icons";
import Image from "next/image";
import { getPost, getSettings, getSitemap } from "@/lib/api";
import { redirectIfMoved } from "@/lib/redirects";
import { formatDate, formatReadingTime } from "@/lib/format";
import { highlightCode } from "@/lib/highlight";
import { blogPostingJsonLd, breadcrumbJsonLd } from "@/lib/jsonld";
import { absoluteUrl, buildMetadata } from "@/lib/seo";

export const dynamicParams = true;

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  return sitemap.posts.map((post) => ({ slug: post.slug }));
}

export async function generateMetadata({
  params,
}: PageProps<"/blog/[slug]">): Promise<Metadata> {
  const { slug } = await params;
  const [post, settings] = await Promise.all([getPost(slug), getSettings()]);
  if (!post) return {};

  return buildMetadata({
    title: post.title,
    seo: post.seo,
    settings,
    path: `/blog/${post.slug}`,
    type: "article",
    publishedTime: post.published_at,
    modifiedTime: post.updated_at,
    fallbackImage: post.cover,
  });
}

export default async function PostPage({ params }: PageProps<"/blog/[slug]">) {
  const { slug } = await params;
  const [post, settings] = await Promise.all([getPost(slug), getSettings()]);
  if (!post) {
    await redirectIfMoved(`/blog/${slug}`);
    notFound();
  }

  const html = await highlightCode(post.body_html);
  const url = absoluteUrl(`/blog/${post.slug}`);

  return (
    <>
      <JsonLd
        data={[
          blogPostingJsonLd(post, settings),
          breadcrumbJsonLd([
            { name: "Home", path: "/" },
            { name: "Blog", path: "/blog" },
            {
              name: post.category.name,
              path: `/blog/category/${post.category.slug}`,
            },
            { name: post.title, path: `/blog/${post.slug}` },
          ]),
        ]}
      />

      <article>
        <header className="bg-bg pt-8 pb-12">
          <Container>
            <Breadcrumbs
              items={[
                { label: "Home", href: "/" },
                { label: "Blog", href: "/blog" },
                {
                  label: post.category.name,
                  href: `/blog/category/${post.category.slug}`,
                },
              ]}
            />
            <div className="mx-auto mt-6 flex max-w-4xl flex-col items-center text-center">
              <Link
                href={`/blog/category/${post.category.slug}`}
                className="rounded-tag bg-chip text-chip-text px-3 py-1.5 text-xs font-medium"
              >
                {post.category.name}
              </Link>
              <h1 className="text-page-h1 tracking-h1 text-text mt-5 leading-[1.15]">
                {post.title}
              </h1>
              <p className="text-lead text-muted mt-5 max-w-2xl">
                {post.excerpt}
              </p>
              <div className="mt-6 flex items-center gap-3 text-start">
                {post.author.photo ? (
                  <Image
                    src={post.author.photo.card ?? post.author.photo.url}
                    alt=""
                    width={40}
                    height={40}
                    className="bg-tile size-10 rounded-full object-cover object-top"
                  />
                ) : null}
                <div>
                  <div className="text-ui text-text font-semibold">
                    {post.author.name}
                  </div>
                  <div className="text-caption text-dim">
                    <time dateTime={post.published_at}>
                      {formatDate(post.published_at)}
                    </time>{" "}
                    · {formatReadingTime(post.reading_time)}
                  </div>
                </div>
              </div>
            </div>

            <div className="rounded-card-lg border-border bg-surface relative mt-10 aspect-[12/5] overflow-hidden border">
              <Media
                image={post.cover}
                size="lg"
                fill
                priority
                sizes="(min-width: 1520px) 1440px, 100vw"
              />
            </div>
          </Container>
        </header>

        <Container className="desktop:grid-cols-[minmax(0,1fr)_50rem_minmax(0,1fr)] desktop:gap-10 grid grid-cols-[minmax(0,1fr)] gap-8 pb-16">
          <aside className="desktop:max-w-50">
            <TableOfContents items={post.toc} />
          </aside>

          <div>
            <RichContent html={html} variant="article" />

            {post.related_service ? (
              <Link
                href={`/services/${post.related_service.slug}`}
                className="rounded-card border-border bg-surface hover:border-accent mt-10 flex items-center gap-4 border p-5 transition-colors"
              >
                <IconTile
                  name={post.related_service.icon}
                  tone="tile"
                  size="sm"
                />
                <span className="grow">
                  <span className="text-ui text-text block font-semibold">
                    Need a fast {post.related_service.nav_label.toLowerCase()}{" "}
                    with a CMS like this?
                  </span>
                  <span className="text-caption text-muted mt-0.5 block">
                    See how I build{" "}
                    {post.related_service.nav_label.toLowerCase()} for
                    businesses.
                  </span>
                </span>
                <ArrowUpRight className="text-dim size-4 shrink-0" />
              </Link>
            ) : null}

            <div className="border-divider mt-10 border-t pt-8">
              {post.tags.length > 0 ? (
                <ul className="flex flex-wrap items-center gap-2.5">
                  <li className="text-caption text-dim me-1">Tags</li>
                  {post.tags.map((tag) => (
                    <li key={tag.slug}>
                      <Tag size="lg">{tag.name}</Tag>
                    </li>
                  ))}
                </ul>
              ) : null}

              <div className="rounded-card border-border bg-surface tablet:flex-row tablet:items-center mt-8 flex flex-col gap-5 border p-6">
                {post.author.photo ? (
                  <Image
                    src={post.author.photo.card ?? post.author.photo.url}
                    alt=""
                    width={64}
                    height={64}
                    className="bg-tile size-16 shrink-0 rounded-full object-cover object-top"
                  />
                ) : null}
                <div className="grow">
                  <div className="text-ui text-text font-semibold">
                    Written by {post.author.name}
                  </div>
                  {post.author.bio ? (
                    <p className="text-caption text-muted mt-1 leading-relaxed">
                      {post.author.bio}
                    </p>
                  ) : null}
                </div>
                <Button
                  href="/about"
                  variant="outline"
                  size="sm"
                  className="shrink-0"
                >
                  About me
                </Button>
              </div>
            </div>
          </div>

          <ShareButtons
            url={url}
            title={post.title}
            className="desktop:sticky desktop:top-24 desktop:self-start desktop:justify-self-end"
          />
        </Container>

        {post.previous || post.next ? (
          <Container as="nav" className="tablet:grid-cols-2 grid gap-4 pb-20">
            {post.previous ? (
              <Link
                href={`/blog/${post.previous.slug}`}
                className="rounded-card border-border bg-surface hover:border-accent flex flex-col gap-1 border p-5 transition-colors"
                rel="prev"
              >
                <span className="text-caption text-dim flex items-center gap-1.5">
                  <ChevronLeft className="size-3.5" /> Previous article
                </span>
                <span className="text-ui text-text font-semibold">
                  {post.previous.title}
                </span>
              </Link>
            ) : (
              <span className="tablet:block hidden" />
            )}
            {post.next ? (
              <Link
                href={`/blog/${post.next.slug}`}
                className="rounded-card border-border bg-surface hover:border-accent flex flex-col gap-1 border p-5 text-end transition-colors"
                rel="next"
              >
                <span className="text-caption text-dim flex items-center justify-end gap-1.5">
                  Next article <ChevronRight className="size-3.5" />
                </span>
                <span className="text-ui text-text font-semibold">
                  {post.next.title}
                </span>
              </Link>
            ) : null}
          </Container>
        ) : null}
      </article>

      {post.related.length > 0 ? (
        <Section variant="alt" aria-labelledby="related-title">
          <div className="flex items-end justify-between gap-4">
            <div>
              <Eyebrow>Keep reading</Eyebrow>
              <h2
                id="related-title"
                className="text-h2 tracking-h2 text-text mt-2.5"
              >
                Related Articles
              </h2>
            </div>
            <Button
              href="/blog"
              variant="outline"
              className="text-ui max-tablet:hidden h-11.5"
            >
              View All <ArrowUpRight className="size-3.5" />
            </Button>
          </div>
          <ul className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-10 grid">
            {post.related.map((related) => (
              <li key={related.slug}>
                <PostCard post={related} />
              </li>
            ))}
          </ul>
        </Section>
      ) : null}
    </>
  );
}
