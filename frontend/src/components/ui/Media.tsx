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
};

/** A Laravel image with its intrinsic size (no layout shift) or filling an aspect-ratio box. */
export function Media({
  image,
  size = "md",
  sizes,
  priority,
  className,
  fill,
}: MediaProps) {
  if (!image) return null;

  const src = image[size] ?? image.md ?? image.url;

  if (fill || !image.width || !image.height) {
    return (
      <Image
        src={src}
        alt={image.alt}
        fill
        sizes={sizes}
        priority={priority}
        className={cn("object-cover", className)}
      />
    );
  }

  return (
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
}
