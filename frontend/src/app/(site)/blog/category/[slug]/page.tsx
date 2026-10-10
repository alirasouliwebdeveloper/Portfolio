import { notFound } from "next/navigation";
import { BlogListPage, blogListMetadata } from "@/components/blog/BlogListPage";
import { getCategory, getSitemap } from "@/lib/api";
import { redirectIfMoved } from "@/lib/redirects";

export const dynamicParams = true;

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  return sitemap.categories.map((category) => ({ slug: category.slug }));
}

export async function generateMetadata({
  params,
}: PageProps<"/blog/category/[slug]">) {
  const category = await getCategory((await params).slug);
  return category ? blogListMetadata({ category }, 1) : {};
}

export default async function CategoryPage({
  params,
}: PageProps<"/blog/category/[slug]">) {
  const { slug } = await params;
  const category = await getCategory(slug);
  if (!category) {
    await redirectIfMoved(`/blog/category/${slug}`);
    notFound();
  }

  return <BlogListPage scope={{ category }} />;
}
