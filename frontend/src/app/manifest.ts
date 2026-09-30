import type { MetadataRoute } from "next";
import { getSettings } from "@/lib/api";

/** Lets the site be added to a phone's home screen. Name/description follow the admin's brand settings. */
export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const settings = await getSettings();

  return {
    name: `${settings.brand.name} — ${settings.profile.headline}`,
    short_name: settings.brand.name,
    description:
      settings.seo.meta_description ?? settings.profile.bio_short ?? undefined,
    start_url: "/",
    display: "standalone",
    background_color: "#0a0b1a",
    theme_color: "#0a0b1a",
    icons: [
      { src: "/icon", sizes: "32x32", type: "image/png" },
      { src: "/apple-icon", sizes: "180x180", type: "image/png" },
    ],
  };
}
