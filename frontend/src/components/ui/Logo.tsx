import Link from "next/link";
import Image from "next/image";
import type { Settings } from "@/types/api";
import { cn } from "@/lib/cn";

/** Uploaded logo when set, otherwise the </> mark with the site name. */
export function Logo({
  brand,
  className,
}: {
  brand: Settings["brand"];
  className?: string;
}) {
  return (
    <Link
      href="/"
      aria-label={`${brand.name} — home`}
      className={cn(
        "text-text tablet:text-[1.3125rem] desktop:text-[1.375rem] flex items-center gap-2.5 text-xl font-semibold transition-transform duration-300 ease-out hover:scale-[1.03]",
        className,
      )}
    >
      {brand.logo ? (
        <Image
          src={brand.logo.url}
          alt={brand.name}
          width={brand.logo.width ?? 160}
          height={brand.logo.height ?? 40}
          className="h-8 w-auto"
          priority
        />
      ) : (
        <>
          <span
            aria-hidden="true"
            className="text-accent-soft font-mono font-semibold"
          >
            &lt;/&gt;
          </span>
          <span>{brand.name}</span>
        </>
      )}
    </Link>
  );
}
