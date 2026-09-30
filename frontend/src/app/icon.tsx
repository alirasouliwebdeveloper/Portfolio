import { ImageResponse } from "next/og";
import { getSettings } from "@/lib/api";

export const size = { width: 32, height: 32 };
export const contentType = "image/png";

// Same token values as opengraph-image.tsx — ImageResponse renders outside the CSS pipeline.
const colors = { accent: "#6d5df5", accent2: "#8272ff" };

/**
 * Fallback browser-tab icon, generated from the brand's initial. layout.tsx sets an explicit
 * `icons.icon` from the uploaded favicon when one exists, which takes precedence over this file
 * convention — this only ever renders when no favicon has been uploaded yet.
 */
export default async function Icon() {
  const settings = await getSettings();
  const letter = settings.brand.name.trim().charAt(0).toUpperCase() || "A";

  return new ImageResponse(
    <div
      style={{
        width: "100%",
        height: "100%",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        borderRadius: 7,
        background: `linear-gradient(135deg, ${colors.accent}, ${colors.accent2})`,
        color: "#fff",
        fontSize: 20,
        fontWeight: 700,
      }}
    >
      {letter}
    </div>,
    size,
  );
}
