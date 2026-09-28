import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
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
