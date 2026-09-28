import { ImageResponse } from "next/og";
import { getSettings } from "@/lib/api";

export const alt = "Site preview";
export const size = { width: 1200, height: 630 };
export const contentType = "image/png";

// ImageResponse renders outside the CSS pipeline, so the brand tokens are repeated here (values from docs/03-design-system.md).
const colors = {
  bg: "#0a0b1a",
  text: "#f4f5fa",
  muted: "#a7aac6",
  accent: "#6d5df5",
  accentSoft: "#8272ff",
};

/** Default share image, used whenever a page and the global SEO settings have no OG image. */
export default async function OpenGraphImage() {
  const settings = await getSettings();

  return new ImageResponse(
    <div
      style={{
        width: "100%",
        height: "100%",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        padding: "0 96px",
        background: `linear-gradient(135deg, ${colors.bg} 40%, #1b1942 100%)`,
        color: colors.text,
      }}
    >
      <div style={{ display: "flex", fontSize: 30, color: colors.accentSoft }}>
        {"</>"} {settings.brand.name}
      </div>
      <div
        style={{
          display: "flex",
          marginTop: 28,
          fontSize: 76,
          fontWeight: 700,
          lineHeight: 1.1,
        }}
      >
        {settings.profile.headline}
      </div>
      {settings.brand.tagline ? (
        <div
          style={{
            display: "flex",
            marginTop: 28,
            fontSize: 32,
            color: colors.muted,
          }}
        >
          {settings.brand.tagline}
        </div>
      ) : null}
      <div
        style={{
          display: "flex",
          position: "absolute",
          insetInlineStart: 0,
          bottom: 0,
          width: "100%",
          height: 12,
          background: colors.accent,
        }}
      />
    </div>,
    size,
  );
}
