import type { Settings } from "@/types/api";
import { cn } from "@/lib/cn";
import { GitHubIcon, InstagramIcon, LinkedInIcon, XIcon } from "./brand-icons";

const networks = {
  github: { label: "GitHub", Icon: GitHubIcon },
  linkedin: { label: "LinkedIn", Icon: LinkedInIcon },
  x: { label: "X", Icon: XIcon },
  instagram: { label: "Instagram", Icon: InstagramIcon },
} as const;

export function SocialLinks({
  socials,
  className,
}: {
  socials: Settings["socials"];
  className?: string;
}) {
  const items = (Object.keys(networks) as (keyof typeof networks)[]).filter(
    (key) => socials[key],
  );
  if (items.length === 0) return null;

  return (
    <ul className={cn("flex items-center gap-3", className)}>
      {items.map((key) => {
        const { label, Icon } = networks[key];
        return (
          <li key={key}>
            <a
              href={socials[key]}
              target="_blank"
              rel="noopener noreferrer"
              aria-label={label}
              className="rounded-control border-border-input text-text-2 hover:border-accent hover:text-text flex size-10 items-center justify-center border transition-[color,border-color,transform] duration-300 ease-out hover:scale-110"
            >
              <Icon className="size-4.5" />
            </a>
          </li>
        );
      })}
    </ul>
  );
}
