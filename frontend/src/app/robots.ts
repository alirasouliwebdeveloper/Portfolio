import type { MetadataRoute } from "next";
import { getSettings } from "@/lib/api";
import { absoluteUrl } from "@/lib/seo";

export default async function robots(): Promise<MetadataRoute.Robots> {
  const settings = await getSettings();

  return settings.site_noindex
    ? { rules: { userAgent: "*", disallow: "/" } }
    : {
        rules: {
          userAgent: "*",
          allow: "/",
          disallow: ["/api/", "/blog/search"],
        },
        sitemap: absoluteUrl("/sitemap.xml"),
      };
}
