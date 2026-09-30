import { cn } from "@/lib/cn";
import { Icon, type IconName } from "./icons";

type IconTileProps = {
  name: IconName;
  tone?: "tile" | "chip" | "accent" | "danger" | "success";
  size?: "default" | "sm" | "lg";
  className?: string;
};

const tones = {
  tile: "bg-tile text-icon-soft",
  chip: "bg-chip text-icon-soft",
  accent: "bg-accent text-white",
  danger: "bg-danger-tile text-danger",
  success: "bg-success-solid text-white",
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
        // "icon-tile" is a hook for .hover-card in globals.css: it gets a little extra life when
        // the card it sits in is hovered, on top of whatever the instance's own className does.
        "icon-tile rounded-tile flex shrink-0 items-center justify-center transition-transform duration-500 ease-out",
        tones[tone],
        sizes[size],
        className,
      )}
    >
      <Icon name={name} className="size-[52%]" />
    </span>
  );
}
