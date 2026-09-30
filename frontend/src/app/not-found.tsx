import type { Metadata } from "next";
import Link from "next/link";
import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { IconTile } from "@/components/ui/IconTile";
import { Media } from "@/components/ui/Media";
import { SearchInput } from "@/components/ui/SearchInput";
import { Section } from "@/components/ui/Section";
import { Icon } from "@/components/ui/icons";
import { getNotFoundPage, getPosts } from "@/lib/api";
import { formatReadingTime } from "@/lib/format";
import { BookOpen } from "lucide-react";

export const metadata: Metadata = {
  title: "Page not found",
  robots: { index: false, follow: true },
};

const quickLinks = [
  { icon: "home" as const, label: "Home", href: "/" },
  { icon: "user" as const, label: "About me", href: "/about" },
  { icon: "grid" as const, label: "Projects", href: "/#projects" },
  { icon: "mail" as const, label: "Contact", href: "/contact" },
];

export default async function NotFound() {
  const [page, popular] = await Promise.all([
    getNotFoundPage(),
    getPosts({ perPage: 3 }),
  ]);

  return (
    <>
      <section
        aria-labelledby="not-found-title"
        className="bg-bg tablet:py-20 desktop:py-24 py-16"
      >
        <Container className="desktop:grid-cols-[minmax(0,1fr)_26rem] grid items-center gap-12">
          <div className="flex flex-col items-start">
            <p
              aria-hidden="true"
              className="text-gradient-404 tracking-h1 tablet:text-[9rem] text-[6rem] leading-none font-bold"
            >
              404
            </p>
            <h1
              id="not-found-title"
              className="text-page-h1 tracking-h1 text-text mt-8 leading-[1.15]"
            >
              {page?.title ?? "This page doesn’t exist"}
            </h1>
            <p className="text-lead text-muted mt-4 max-w-160">
              {page?.content.text}
            </p>
            <SearchInput
              visibleLabel
              label="Search the site"
              placeholder="Articles, projects, services…"
              className="mt-8 w-full max-w-130"
            />
            <div className="tablet:w-auto tablet:flex-row mt-6 flex w-full flex-col gap-3">
              <Button href="/" fullWidth className="tablet:w-auto">
                Back to Home <Icon name="home" className="size-4" />
              </Button>
              <Button
                href="/blog"
                variant="outline"
                fullWidth
                className="tablet:w-auto"
              >
                Browse the Blog{" "}
                <BookOpen
                  className="size-4"
                  strokeWidth={1.8}
                  aria-hidden="true"
                />
              </Button>
            </div>
          </div>

          <div
            aria-hidden="true"
            dir="ltr"
            className="rounded-card border-border-input bg-surface-input shadow-float desktop:block hidden border font-mono text-sm"
          >
            <div className="border-divider text-text-2 flex items-center gap-2 border-b px-4 py-3 font-sans text-xs">
              <span className="bg-danger size-2.5 rounded-full" />
              <span className="bg-star size-2.5 rounded-full" />
              <span className="bg-online size-2.5 rounded-full" />
              <span className="ms-3">routes/web.php</span>
            </div>
            <pre className="text-code m-0 p-5 leading-[1.9]">
              <span className="text-dim">{"// this page wandered off"}</span>
              {"\nRoute::"}
              <span className="text-link">fallback</span>
              {"(fn() =>\n    "}
              <span className="text-link">abort</span>
              {"("}
              <span className="text-code-string">404</span>
              {")\n);"}
            </pre>
          </div>
        </Container>
      </section>

      <Section variant="alt" aria-labelledby="quick-links-title">
        <div className="desktop:grid-cols-[1fr_1.15fr] desktop:gap-14 grid gap-12">
          <div>
            <h2
              id="quick-links-title"
              className="text-card-title-lg text-text font-semibold"
            >
              Quick links
            </h2>
            <ul className="tablet:grid-cols-2 mt-6 grid gap-4">
              {quickLinks.map((link) => (
                <li key={link.label}>
                  <Link
                    href={link.href}
                    className="hover-card rounded-card border-border bg-surface text-body text-text hover:border-accent flex items-center gap-4 border p-5 font-medium transition-colors"
                  >
                    <IconTile name={link.icon} tone="tile" size="sm" />
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
          <div>
            <h2 className="text-card-title-lg text-text font-semibold">
              Popular articles
            </h2>
            <ul className="mt-6 flex flex-col gap-4">
              {popular.data.map((post) => (
                <li key={post.slug}>
                  <Link
                    href={`/blog/${post.slug}`}
                    className="hover-card rounded-card border-border bg-surface hover:border-accent flex items-center gap-4 border p-4 transition-colors"
                  >
                    <span className="rounded-control relative size-14 shrink-0 overflow-hidden">
                      <Media
                        image={post.cover}
                        size="card"
                        fill
                        sheen
                        sizes="56px"
                      />
                    </span>
                    <span>
                      <span className="text-ui text-text block font-medium">
                        {post.title}
                      </span>
                      <span className="text-caption text-dim mt-1 block">
                        {formatReadingTime(post.reading_time)}
                      </span>
                    </span>
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </Section>
    </>
  );
}
