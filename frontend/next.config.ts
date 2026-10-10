import type { NextConfig } from "next";

const origin = (url?: string) => {
  try {
    return url ? new URL(url).origin : "";
  } catch {
    return "";
  }
};

// Laravel serves uploads/media from its own origin; the browser talks to it for uploads.
const laravel = origin(process.env.NEXT_PUBLIC_UPLOAD_URL);
const analytics =
  "https://www.googletagmanager.com https://*.google-analytics.com";

/** Next.js needs inline bootstrap scripts, so scripts are limited by origin rather than by nonce. */
const csp = [
  "default-src 'self'",
  `script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com ${analytics}`,
  "style-src 'self' 'unsafe-inline'",
  `img-src 'self' data: blob: https: ${laravel}`,
  "font-src 'self' data:",
  `connect-src 'self' ${laravel} https://challenges.cloudflare.com ${analytics}`,
  "frame-src https://challenges.cloudflare.com",
  "object-src 'none'",
  "base-uri 'self'",
  "form-action 'self'",
  "frame-ancestors 'none'",
].join("; ");

const securityHeaders = [
  { key: "X-Content-Type-Options", value: "nosniff" },
  { key: "X-Frame-Options", value: "DENY" },
  { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
  {
    key: "Permissions-Policy",
    value: "camera=(), microphone=(), geolocation=(), interest-cohort=()",
  },
  ...(process.env.NODE_ENV === "production"
    ? [
        {
          key: "Strict-Transport-Security",
          value: "max-age=63072000; includeSubDomains; preload",
        },
        { key: "Content-Security-Policy", value: csp },
      ]
    : []),
];

const nextConfig: NextConfig = {
  output: "standalone",
  poweredByHeader: false,
  // Keeps on-demand revalidation across Node restarts (Passenger idles the app on cPanel).
  // Relative to this folder; an absolute process.cwd() path made Turbopack trace the whole project.
  cacheHandler: "./cache-handler.cjs",
  async headers() {
    return [{ source: "/:path*", headers: securityHeaders }];
  },
  async redirects() {
    // Page 1 of a list lives at the list root (spec: 301, not the default 308).
    return [
      { source: "/blog/page/1", destination: "/blog", statusCode: 301 },
      {
        source: "/blog/category/:slug/page/1",
        destination: "/blog/category/:slug",
        statusCode: 301,
      },
    ];
  },
  images: {
    // Laravel already serves resized WebP conversions, so Next's image optimizer (and its
    // sharp dependency) is not needed. `next/image` still gives lazy loading and sizing.
    unoptimized: true,
  },
};

export default nextConfig;
