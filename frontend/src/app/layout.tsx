import type { Metadata, Viewport } from "next";
import localFont from "next/font/local";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { SiteHeader } from "@/components/layout/SiteHeader";
import { CookieConsent } from "@/components/ui/CookieConsent";
import { GoogleAnalytics } from "@/components/ui/GoogleAnalytics";
import { getSettings } from "@/lib/api";
import "./globals.css";

// Self-hosted (npm packages) so the site never depends on a font CDN.
const inter = localFont({
  src: "../../node_modules/@fontsource-variable/inter/files/inter-latin-wght-normal.woff2",
  variable: "--font-inter",
  weight: "100 900",
  display: "swap",
});

const jetbrainsMono = localFont({
  src: "../../node_modules/@fontsource/jetbrains-mono/files/jetbrains-mono-latin-400-normal.woff2",
  variable: "--font-jetbrains",
  weight: "400",
  display: "swap",
});

// Matches manifest.ts's background/theme color so the browser chrome and the installed app agree.
export const viewport: Viewport = { themeColor: "#0a0b1a" };

export async function generateMetadata(): Promise<Metadata> {
  const settings = await getSettings();
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

  return {
    metadataBase: new URL(siteUrl),
    title: {
      default:
        settings.seo.meta_title ??
        `${settings.brand.name} — Full-Stack Developer`,
      template: `%s — ${settings.brand.name} | Full-Stack Developer`,
    },
    description: settings.seo.meta_description ?? undefined,
    applicationName: settings.brand.name,
    icons: settings.brand.favicon
      ? { icon: settings.brand.favicon }
      : undefined,
    verification: settings.tracking.gsc_verification
      ? { google: settings.tracking.gsc_verification }
      : undefined,
    robots: settings.site_noindex ? { index: false, follow: false } : undefined,
    alternates: {
      types: { "application/rss+xml": "/blog/rss.xml" },
    },
  };
}

export default async function RootLayout({ children }: LayoutProps<"/">) {
  const settings = await getSettings();

  return (
    <html
      lang="en"
      dir="ltr"
      className={`${inter.variable} ${jetbrainsMono.variable} h-full antialiased`}
    >
      <body className="flex min-h-full flex-col">
        <a
          href="#main"
          className="focus:rounded-control focus:bg-accent sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:px-4 focus:py-2 focus:text-white"
        >
          Skip to content
        </a>
        <SiteHeader />
        <main id="main" className="flex-1">
          {children}
        </main>
        <SiteFooter />
        <GoogleAnalytics id={settings.tracking.ga_measurement_id} />
        <CookieConsent
          hasAnalytics={Boolean(settings.tracking.ga_measurement_id)}
        />
      </body>
    </html>
  );
}
