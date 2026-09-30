import { getBlogPage, getPosts, getSettings } from "@/lib/api";
import { absoluteUrl, siteUrl } from "@/lib/seo";

export const dynamic = "force-static";

const escape = (value: string) =>
  value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&apos;");

/** RSS 2.0 feed of the latest posts — linked from <head> via layout.tsx and /blog's page metadata. */
export async function GET() {
  const [settings, blogPage, posts] = await Promise.all([
    getSettings(),
    getBlogPage(),
    getPosts({ perPage: 20 }),
  ]);

  const title = settings.brand.name;
  const description =
    blogPage?.content.description ?? settings.seo.meta_description ?? "";
  const items = posts.data
    .map((post) => {
      const url = absoluteUrl(`/blog/${post.slug}`);
      return `
    <item>
      <title>${escape(post.title)}</title>
      <link>${url}</link>
      <guid isPermaLink="true">${url}</guid>
      <pubDate>${new Date(post.published_at).toUTCString()}</pubDate>
      <category>${escape(post.category.name)}</category>
      <description>${escape(post.excerpt)}</description>
    </item>`;
    })
    .join("");

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>${escape(title)} — Blog</title>
    <link>${absoluteUrl("/blog")}</link>
    <atom:link href="${absoluteUrl("/blog/rss.xml")}" rel="self" type="application/rss+xml" />
    <description>${escape(description)}</description>
    <language>en</language>
    <generator>${escape(siteUrl())}</generator>${items}
  </channel>
</rss>`;

  return new Response(xml, {
    headers: {
      "Content-Type": "application/rss+xml; charset=utf-8",
      // Same safety-net window as the rest of the site's cached content (docs/02-architecture.md).
      "Cache-Control":
        "public, max-age=0, s-maxage=86400, stale-while-revalidate",
    },
  });
}
