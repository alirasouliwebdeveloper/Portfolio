import { cn } from "@/lib/cn";

/**
 * Renders sanitized HTML produced by the API (Filament rich editor). Typography lives in
 * globals.css under `.rich-content`; code blocks stay left-to-right in RTL layouts.
 */
export function RichContent({
  html,
  className,
  variant = "body",
}: {
  html: string;
  className?: string;
  variant?: "body" | "article";
}) {
  if (!html) return null;

  return (
    <div
      className={cn(
        "rich-content",
        variant === "article" && "rich-content--article",
        className,
      )}
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}
