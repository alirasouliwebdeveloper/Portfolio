import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import { BlogHeader } from "@/components/blog/BlogHeader";
import { PostCard } from "@/components/blog/PostCard";
import { PostGrid } from "@/components/blog/PostGrid";
import { SortSelect } from "@/components/blog/SortSelect";
import { Button } from "@/components/ui/Button";
import { SearchInput } from "@/components/ui/SearchInput";
import { FilterChip } from "@/components/ui/Chip";
import { Card } from "@/components/ui/Card";
import { Container } from "@/components/ui/Container";
import { IconTile } from "@/components/ui/IconTile";
import { Section } from "@/components/ui/Section";
import { ArrowUpRight, Icon } from "@/components/ui/icons";
import { getCategories, getPosts, getSettings, searchPosts } from "@/lib/api";

// Rendered per request (results depend on the query), never indexed, never in the sitemap.
export const metadata: Metadata = {
  title: "Search",
  robots: { index: false, follow: true },
};

const first = (value: string | string[] | undefined) =>
  (Array.isArray(value) ? value[0] : value) ?? "";

const tips = [
  "Check the spelling",
  "Use fewer or more general words",
  "Search for a tool, like “Docker” or “Filament”",
];

export default async function BlogSearchPage({
  searchParams,
}: PageProps<"/blog/search">) {
  const params = await searchParams;
  const query = first(params.q).trim().slice(0, 100);
  if (query.length < 2) redirect("/blog");

  const category = first(params.category) || undefined;
  const sort = first(params.sort) === "newest" ? "newest" : "relevance";
  const page = Math.max(1, Number.parseInt(first(params.page), 10) || 1);

  const result = await searchPosts({ q: query, category, sort, page });
  const href = (target: number) => {
    const search = new URLSearchParams({ q: query });
    if (category) search.set("category", category);
    if (sort !== "relevance") search.set("sort", sort);
    if (target > 1) search.set("page", String(target));
    return `/blog/search?${search.toString()}`;
  };

  const breadcrumbs = [
    { label: "Home", href: "/" },
    { label: "Blog", href: "/blog" },
    { label: "Search" },
  ];

  if (result.meta.total_all === 0) {
    const [settings, categories, popular] = await Promise.all([
      getSettings(),
      getCategories(),
      getPosts({ perPage: 3 }),
    ]);

    return (
      <>
        <BlogHeader
          breadcrumbs={breadcrumbs}
          eyebrow="Search"
          title={
            <>
              No results for <span className="text-icon-soft">“{query}”</span>
            </>
          }
          description="Try a different word, check the spelling, or browse by topic below."
          search={false}
        />
        <Section className="pt-10 pb-12" flush>
          <Container>
            <Card
              radius="lg"
              className="desktop:grid-cols-[1fr_1.4fr] desktop:gap-16 grid gap-10"
            >
              <div className="flex flex-col items-start gap-4">
                <IconTile name="search" tone="tile" size="lg" />
                <h2 className="text-card-title-lg text-text font-semibold">
                  Nothing matched your search
                </h2>
                <ul className="flex flex-col gap-3">
                  {tips.map((tip) => (
                    <li
                      key={tip}
                      className="text-ui text-text-2 flex items-center gap-3"
                    >
                      <Icon name="check" className="text-success size-4.5" />
                      {tip}
                    </li>
                  ))}
                </ul>
                <Button href="/blog" variant="outline" size="sm">
                  View All Articles <ArrowUpRight className="size-3.5" />
                </Button>
              </div>
              <div className="flex flex-col gap-8">
                {settings.popular_searches.length > 0 ? (
                  <div>
                    <h2 className="text-ui text-text font-semibold">
                      Popular searches
                    </h2>
                    <ul className="mt-4 flex flex-wrap gap-2.5">
                      {settings.popular_searches.map((term) => (
                        <li key={term}>
                          <Link
                            href={`/blog/search?q=${encodeURIComponent(term)}`}
                            className="rounded-pill border-border-input text-text-2 hover:border-accent flex items-center gap-2 border px-4 py-2 text-sm transition-colors"
                          >
                            <Icon name="search" className="size-3.5" /> {term}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </div>
                ) : null}
                <div>
                  <h2 className="text-ui text-text font-semibold">
                    Browse by topic
                  </h2>
                  <ul className="tablet:grid-cols-2 desktop:grid-cols-3 mt-4 grid gap-3">
                    {categories.map((item) => (
                      <li key={item.slug}>
                        <Link
                          href={`/blog/category/${item.slug}`}
                          className="rounded-card border-border bg-surface text-ui text-text hover:border-accent flex items-center justify-between border px-4 py-3 font-medium transition-colors"
                        >
                          {item.name}
                          <span className="text-caption text-dim font-normal">
                            {item.posts_count} articles
                          </span>
                        </Link>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            </Card>
          </Container>
        </Section>
        <Section variant="alt" aria-labelledby="popular-title">
          <h2 id="popular-title" className="text-h2 tracking-h2 text-text">
            Popular articles
          </h2>
          <ul className="gap-grid tablet:grid-cols-2 desktop:grid-cols-3 mt-8 grid">
            {popular.data.map((post) => (
              <li key={post.slug}>
                <PostCard post={post} />
              </li>
            ))}
          </ul>
        </Section>
      </>
    );
  }

  const chips = [
    {
      label: "All",
      href: href(1).replace(/&category=[^&]*/, ""),
      count: result.meta.total_all,
      active: !category,
    },
    ...result.categories.map((item) => ({
      label: item.name,
      href: `/blog/search?${new URLSearchParams({ q: query, category: item.slug }).toString()}`,
      count: item.count,
      active: category === item.slug,
    })),
  ];

  return (
    <>
      <BlogHeader
        breadcrumbs={breadcrumbs}
        eyebrow="Search"
        title={
          <>
            Results for <span className="text-icon-soft">“{query}”</span>
          </>
        }
        search={false}
      >
        <p className="text-subhead text-muted" aria-live="polite">
          {result.meta.total_all}{" "}
          {result.meta.total_all === 1 ? "article" : "articles"} found
        </p>
        <SearchInput
          withButton
          defaultValue={query}
          clearHref="/blog"
          className="mt-7 max-w-190"
        />
      </BlogHeader>
      <PostGrid
        toolbar={
          <>
            <ul className="flex flex-wrap gap-2.5">
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
            <SortSelect query={query} category={category} value={sort} />
          </>
        }
        posts={result.data}
        meta={result.meta}
        href={href}
        query={query}
      />
    </>
  );
}
