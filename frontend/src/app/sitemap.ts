import type { MetadataRoute } from "next";
import { getSettings, getSitemap } from "@/lib/api";
import { absoluteUrl } from "@/lib/seo";

/** Built from the API so new posts, projects and services appear as soon as they are published. */
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [settings, map] = await Promise.all([getSettings(), getSitemap()]);
  if (settings.site_noindex) return [];

  const entry = (path: string, lastmod: string, priority: number) => ({
    url: absoluteUrl(path),
    lastModified: lastmod,
    priority,
  });

  return [
    ...map.pages.map((page) =>
      entry(page.path, page.lastmod, page.path === "/" ? 1 : 0.8),
    ),
    ...map.services.map((item) =>
      entry(`/services/${item.slug}`, item.lastmod, 0.8),
    ),
    ...map.projects.map((item) =>
      entry(`/projects/${item.slug}`, item.lastmod, 0.7),
    ),
    ...map.categories
      .filter((item) => item.posts_count > 0)
      .map((item) => entry(`/blog/category/${item.slug}`, item.lastmod, 0.5)),
    ...map.posts.map((item) => entry(`/blog/${item.slug}`, item.lastmod, 0.6)),
  ];
}
