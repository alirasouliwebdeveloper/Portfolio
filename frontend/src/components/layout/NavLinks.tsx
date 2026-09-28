"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/cn";
import { mainNav } from "@/lib/nav";

export function NavLinks({
  className,
  linkClassName,
}: {
  className?: string;
  linkClassName?: string;
}) {
  const pathname = usePathname();

  return (
    <nav aria-label="Main" className={className}>
      <ul className="desktop:flex-row desktop:gap-11 flex flex-col gap-2">
        {mainNav.map((item) => {
          const active = item.matches(pathname);
          return (
            <li key={item.label}>
              <Link
                href={item.href}
                aria-current={active ? "page" : undefined}
                className={cn(
                  "inline-block border-b-2 py-2 text-base transition-colors",
                  active
                    ? "border-accent text-white"
                    : "text-text-2 hover:text-text border-transparent",
                  linkClassName,
                )}
              >
                {item.label}
              </Link>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
