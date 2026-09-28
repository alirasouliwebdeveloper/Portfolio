import type { Metadata } from "next";
import { notFound } from "next/navigation";
import {
  getBlogPage,
  getCategories,
  getFeaturedPost,
  getPosts,
  getSettings,
} from "@/lib/api";
import { buildMetadata } from "@/lib/seo";
import type { Category } from "@/types/api";
import { BlogHeader } from "./BlogHeader";
import { FeaturedPost } from "./FeaturedPost";
import { PostGrid } from "./PostGrid";

type Scope = { category?: Category };

const rootPath = (scope: Scope) =>
  scope.category ? `/blog/category/${scope.category.slug}` : "/blog";
export const pageHref = (scope: Scope) => (page: number) =>
  page <= 1 ? rootPath(scope) : `${rootPath(scope)}/page/${page}`;

/** Metadata for /blog, /blog/page/n and the category routes. Page n has its own canonical URL. */
export async function blogListMetadata(
  scope: Scope,
  page: number,
): Promise<Metadata> {
  const [blogPage, settings] = await Promise.all([
    getBlogPage(),
    getSettings(),
  ]);
  if (!blogPage) return {};

  const base = scope.category
    ? { title: scope.category.name, seo: scope.category.seo }
    : { title: blogPage.title, seo: blogPage.seo };
  const title = page > 1 ? `${base.title} — Page ${page}` : base.title;

  return buildMetadata({
    title,
    seo:
      page > 1
        ? {
            ...base.seo,
            meta_title: base.seo.meta_title
              ? `${base.seo.meta_title} — Page ${page}`
              : null,
          }
        : base.seo,
    settings,
    path: pageHref(scope)(page),
  });
}

/** One template for the blog root, numbered pages, and category pages (docs/01-product-spec.md). */
export async function BlogListPage({
  scope = {},
  page = 1,
}: {
  scope?: Scope;
  page?: number;
}) {
  const slug = scope.category?.slug;
  const [blogPage, categories, posts, featured] = await Promise.all([
    getBlogPage(),
    getCategories(),
    getPosts({ page, category: slug, excludeFeatured: true }),
    page === 1 ? getFeaturedPost(slug) : Promise.resolve(null),
  ]);
  if (!blogPage || page > Math.max(posts.meta.last_page, 1)) notFound();

  const total = categories.reduce(
    (sum, category) => sum + category.posts_count,
    0,
  );
  const chips = [
    { label: "All", href: "/blog", count: total, active: !scope.category },
    ...categories.map((category) => ({
      label: category.name,
      href: `/blog/category/${category.slug}`,
      count: category.posts_count,
      active: category.slug === slug,
    })),
  ];

  const title = scope.category ? scope.category.name : blogPage.title;
  const suffix = page > 1 ? ` — Page ${page}` : "";

  return (
    <>
      <BlogHeader
        breadcrumbs={
          scope.category
            ? [
                { label: "Home", href: "/" },
                { label: "Blog", href: "/blog" },
                { label: scope.category.name },
              ]
            : [{ label: "Home", href: "/" }, { label: "Blog" }]
        }
        eyebrow={scope.category ? "Category" : blogPage.content.eyebrow}
        title={`${title}${suffix}`}
        description={
          scope.category
            ? scope.category.description
            : blogPage.content.description
        }
        chips={chips}
        compact={page > 1}
      />
      {featured ? <FeaturedPost post={featured} /> : null}
      <PostGrid
        heading={page > 1 ? "Articles" : "Latest Articles"}
        posts={posts.data}
        meta={posts.meta}
        href={pageHref(scope)}
      />
    </>
  );
}
