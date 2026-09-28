import Link from "next/link";
import type {
  AnchorHTMLAttributes,
  ButtonHTMLAttributes,
  ReactNode,
} from "react";
import { cn } from "@/lib/cn";

export type ButtonVariant = "primary" | "outline";
export type ButtonSize = "md" | "sm" | "input" | "nav";

type StyleProps = {
  variant?: ButtonVariant;
  size?: ButtonSize;
  fullWidth?: boolean;
  className?: string;
};

const base =
  "inline-flex items-center justify-center gap-3 rounded-control font-medium whitespace-nowrap transition-colors aria-disabled:pointer-events-none aria-disabled:opacity-50 disabled:pointer-events-none disabled:opacity-50";

const variants: Record<ButtonVariant, string> = {
  primary: "bg-primary-gradient text-white hover:brightness-110",
  outline: "border border-text-2 text-white hover:border-white",
};

const sizes: Record<ButtonSize, string> = {
  md: "h-control px-6.5 text-base",
  sm: "h-12 px-6.5 text-ui",
  input: "h-12.5 px-6.5 text-base",
  nav: "h-11 gap-2 px-4.5 text-ui desktop:h-11.5 desktop:px-5.5",
};

export function buttonClasses({
  variant = "primary",
  size = "md",
  fullWidth,
  className,
}: StyleProps = {}) {
  return cn(
    base,
    variants[variant],
    sizes[size],
    fullWidth && "w-full",
    className,
  );
}

type ButtonAsLink = StyleProps &
  Omit<AnchorHTMLAttributes<HTMLAnchorElement>, "className" | "href"> & {
    href: string;
    children?: ReactNode;
  };

type ButtonAsButton = StyleProps &
  Omit<ButtonHTMLAttributes<HTMLButtonElement>, "className"> & {
    href?: undefined;
  };

export type ButtonProps = ButtonAsLink | ButtonAsButton;

const isInternal = (href: string) =>
  href.startsWith("/") || href.startsWith("#");

export function Button(props: ButtonProps) {
  const { variant, size, fullWidth, className, ...rest } = props;
  const classes = buttonClasses({ variant, size, fullWidth, className });

  if (rest.href !== undefined) {
    const { href, ...anchor } = rest as Omit<ButtonAsLink, keyof StyleProps>;
    if (isInternal(href)) {
      return <Link href={href} className={classes} {...anchor} />;
    }
    return (
      <a
        href={href}
        className={classes}
        {...(anchor.target === "_blank" ? { rel: "noopener noreferrer" } : {})}
        {...anchor}
      />
    );
  }

  const { type = "button", ...button } = rest as Omit<
    ButtonAsButton,
    keyof StyleProps
  >;
  return <button type={type} className={classes} {...button} />;
}
