import { cn } from "@/lib/cn";
import { Icon, type IconName } from "./icons";

type IconTileProps = {
  name: IconName;
  tone?: "tile" | "chip" | "accent";
  size?: "default" | "sm" | "lg";
  className?: string;
};

const tones = {
  tile: "bg-tile text-icon-soft",
  chip: "bg-chip text-icon-soft",
  accent: "bg-accent text-white",
} as const;

const sizes = {
  default: "size-tile",
  sm: "size-10.5",
  lg: "size-16",
} as const;

export function IconTile({
  name,
  tone = "tile",
  size = "default",
  className,
}: IconTileProps) {
  return (
    <span
      className={cn(
        "rounded-tile flex shrink-0 items-center justify-center",
        tones[tone],
        sizes[size],
        className,
      )}
    >
      <Icon name={name} className="size-[52%]" />
    </span>
  );
}
