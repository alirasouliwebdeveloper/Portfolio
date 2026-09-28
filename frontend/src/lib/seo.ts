import type { Metadata } from "next";
import type { ApiImage, PageContent, Seo, Settings } from "@/types/api";

export const siteUrl = () =>
  (process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000").replace(
    /\/$/,
    "",
  );

export const absoluteUrl = (path: string) =>
  `${siteUrl()}${path.startsWith("/") ? path : `/${path}`}`;

const imageFor = (image: ApiImage | null | undefined) =>
  image
    ? [
        {
          url: image.lg ?? image.md ?? image.url,
          width: image.width ?? undefined,
          height: image.height ?? undefined,
          alt: image.alt,
        },
      ]
    : undefined;

type MetaInput = {
  title: string;
  seo: Seo;
  settings: Settings;
  path: string;
  /** Use the meta title as-is (home) instead of appending the site name. */
  absoluteTitle?: boolean;
  type?: "website" | "article";
  publishedTime?: string;
  modifiedTime?: string;
  fallbackImage?: ApiImage | null;
};

/** Title, description, canonical, Open Graph and Twitter tags with the fallbacks of docs/05-seo-and-content.md. */
export function buildMetadata({
  title,
  seo,
  settings,
  path,
  absoluteTitle,
  type = "website",
  publishedTime,
  modifiedTime,
  fallbackImage,
}: MetaInput): Metadata {
  const description =
    seo.meta_description ?? settings.seo.meta_description ?? undefined;
  const image = seo.og_image ?? fallbackImage ?? settings.seo.og_image;
  const resolvedTitle = seo.meta_title
    ? { absolute: seo.meta_title }
    : absoluteTitle
      ? { absolute: title }
      : title;
  const shownTitle =
    seo.meta_title ??
    (absoluteTitle
      ? title
      : `${title} — ${settings.brand.name} | Full-Stack Developer`);
  const canonical = seo.canonical_url ?? absoluteUrl(path);

  return {
    title: resolvedTitle,
    description,
    alternates: { canonical },
    robots:
      seo.noindex || settings.site_noindex
        ? { index: false, follow: false }
        : undefined,
    openGraph: {
      type,
      url: canonical,
      title: shownTitle,
      description,
      siteName: settings.brand.name,
      images: imageFor(image),
      ...(type === "article" ? { publishedTime, modifiedTime } : {}),
    },
    twitter: {
      card: image ? "summary_large_image" : "summary",
      title: shownTitle,
      description,
      site: settings.tracking.twitter_handle
        ? `@${settings.tracking.twitter_handle}`
        : undefined,
      images: image ? [image.lg ?? image.md ?? image.url] : undefined,
    },
  };
}

export const pageMetadata = ({
  page,
  settings,
  path,
  absoluteTitle,
}: {
  page: PageContent<unknown>;
  settings: Settings;
  path: string;
  absoluteTitle?: boolean;
}) =>
  buildMetadata({
    title: page.title,
    seo: page.seo,
    settings,
    path,
    absoluteTitle,
  });
