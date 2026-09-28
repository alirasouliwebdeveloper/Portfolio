import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  images: {
    // Laravel already serves resized WebP conversions, so Next's image optimizer (and its
    // sharp dependency) is not needed. `next/image` still gives lazy loading and sizing.
    unoptimized: true,
  },
};

export default nextConfig;
