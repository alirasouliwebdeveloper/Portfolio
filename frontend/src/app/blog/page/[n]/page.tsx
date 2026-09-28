import { notFound } from "next/navigation";
import { BlogListPage, blogListMetadata } from "@/components/blog/BlogListPage";
import { getSitemap } from "@/lib/api";

export const dynamicParams = true;

const parse = (raw: string) => (/^\d+$/.test(raw) ? Number(raw) : NaN);

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  const pages = Math.ceil(sitemap.posts.length / sitemap.posts_per_page);
  return Array.from({ length: Math.max(pages - 1, 0) }, (_, index) => ({
    n: String(index + 2),
  }));
}

export async function generateMetadata({
  params,
}: PageProps<"/blog/page/[n]">) {
  const page = parse((await params).n);
  return Number.isNaN(page) || page < 2 ? {} : blogListMetadata({}, page);
}

export default async function BlogPagedPage({
  params,
}: PageProps<"/blog/page/[n]">) {
  const page = parse((await params).n);
  // /blog/page/1 is redirected (301) by next.config.ts; anything else invalid is a 404.
  if (Number.isNaN(page) || page < 2) notFound();

  return <BlogListPage page={page} />;
}
