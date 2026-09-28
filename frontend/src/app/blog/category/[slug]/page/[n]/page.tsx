import { notFound } from "next/navigation";
import { BlogListPage, blogListMetadata } from "@/components/blog/BlogListPage";
import { getCategory, getSitemap } from "@/lib/api";

export const dynamicParams = true;

const parse = (raw: string) => (/^\d+$/.test(raw) ? Number(raw) : NaN);

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  return sitemap.categories.flatMap((category) => {
    const pages = Math.ceil(category.posts_count / sitemap.posts_per_page);
    return Array.from({ length: Math.max(pages - 1, 0) }, (_, index) => ({
      slug: category.slug,
      n: String(index + 2),
    }));
  });
}

export async function generateMetadata({
  params,
}: PageProps<"/blog/category/[slug]/page/[n]">) {
  const { slug, n } = await params;
  const [category, page] = [await getCategory(slug), parse(n)];
  return category && !Number.isNaN(page) && page >= 2
    ? blogListMetadata({ category }, page)
    : {};
}

export default async function CategoryPagedPage({
  params,
}: PageProps<"/blog/category/[slug]/page/[n]">) {
  const { slug, n } = await params;
  const [category, page] = [await getCategory(slug), parse(n)];
  if (!category || Number.isNaN(page) || page < 2) notFound();

  return <BlogListPage scope={{ category }} page={page} />;
}
