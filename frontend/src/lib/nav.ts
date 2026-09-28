export type NavItem = {
  label: string;
  href: string;
  matches: (pathname: string) => boolean;
};

/** "Projects" has no index page yet: it points at the Featured Projects section on Home. */
export const mainNav: NavItem[] = [
  { label: "Home", href: "/", matches: (p) => p === "/" },
  { label: "About", href: "/about", matches: (p) => p.startsWith("/about") },
  {
    label: "Projects",
    href: "/#projects",
    matches: (p) => p.startsWith("/projects"),
  },
  { label: "Blog", href: "/blog", matches: (p) => p.startsWith("/blog") },
  {
    label: "Contact",
    href: "/contact",
    matches: (p) => p.startsWith("/contact"),
  },
];
