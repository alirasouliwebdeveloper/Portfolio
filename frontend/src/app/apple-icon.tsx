import { ImageResponse } from "next/og";
import { getSettings } from "@/lib/api";

export const size = { width: 180, height: 180 };
export const contentType = "image/png";

const colors = { accent: "#6d5df5", accent2: "#8272ff" };

/**
 * iOS home-screen icon: Safari reads `<link rel="apple-icon">` directly rather than the web
 * manifest, so this needs its own file even though it's otherwise the same mark as icon.tsx.
 * iOS applies its own corner rounding, so this stays a plain square.
 */
export default async function AppleIcon() {
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
        background: `linear-gradient(135deg, ${colors.accent}, ${colors.accent2})`,
        color: "#fff",
        fontSize: 96,
        fontWeight: 700,
      }}
    >
      {letter}
    </div>,
    size,
  );
}
