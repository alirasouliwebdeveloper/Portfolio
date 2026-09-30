import Image from "next/image";
import type { ApiImage } from "@/types/api";
import { cn } from "@/lib/cn";

type MediaProps = {
  image: ApiImage | null;
  /** Which converted size to use; falls back to the original. */
  size?: "card" | "md" | "lg";
  sizes: string;
  priority?: boolean;
  className?: string;
  /** "fill" needs a positioned, sized parent; otherwise intrinsic dimensions are used. */
  fill?: boolean;
  /** Wraps the image so a diagonal light streak sweeps across it on hover (its own or an ancestor `.hover-card`'s). */
  sheen?: boolean;
};

/** A Laravel image with its intrinsic size (no layout shift) or filling an aspect-ratio box. */
export function Media({
  image,
  size = "md",
  sizes,
  priority,
  className,
  fill,
  sheen,
}: MediaProps) {
  if (!image) return null;

  const src = image[size] ?? image.md ?? image.url;

  const img =
    fill || !image.width || !image.height ? (
      <Image
        src={src}
        alt={image.alt}
        fill
        sizes={sizes}
        priority={priority}
        className={cn("object-cover", className)}
      />
    ) : (
      <Image
        src={src}
        alt={image.alt}
        width={size === "card" ? 800 : image.width}
        height={size === "card" ? 500 : image.height}
        sizes={sizes}
        priority={priority}
        className={className}
      />
    );

  if (!sheen) return img;

  // `absolute inset-0` when filling: this becomes the positioned ancestor next/image looks for,
  // sized by the caller's own relative box, so wrapping it here needs no changes at call sites.
  return (
    <span
      className={cn("hover-sheen", fill ? "absolute inset-0" : "inline-block")}
    >
      {img}
    </span>
  );
}
