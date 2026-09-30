import Link from "next/link";
import { useId } from "react";
import { cn } from "@/lib/cn";
import { Button } from "./Button";
import { controlClasses } from "./FormField";
import { Icon } from "./icons";

type SearchInputProps = {
  action?: string;
  defaultValue?: string;
  label?: string;
  placeholder?: string;
  visibleLabel?: boolean;
  withButton?: boolean;
  clearHref?: string;
  className?: string;
};

export function SearchInput({
  action = "/blog/search",
  defaultValue,
  label = "Search articles",
  placeholder,
  visibleLabel,
  withButton,
  clearHref,
  className,
}: SearchInputProps) {
  const id = useId();
  const hasValue = Boolean(defaultValue);

  return (
    <form
      role="search"
      action={action}
      method="get"
      className={cn(
        withButton ? "flex gap-3" : "flex flex-col gap-2",
        className,
      )}
    >
      <label
        htmlFor={id}
        className={visibleLabel ? "text-caption text-muted" : "sr-only"}
      >
        {label}
      </label>
      <div className={cn("relative", withButton && "grow")}>
        <span className="text-dim pointer-events-none absolute start-4 top-1/2 -translate-y-1/2">
          <Icon name="search" className="size-5" />
        </span>
        <input
          id={id}
          type="search"
          name="q"
          defaultValue={defaultValue}
          placeholder={placeholder}
          minLength={2}
          maxLength={100}
          className={cn(
            controlClasses,
            "h-12.5 ps-12 [&::-webkit-search-cancel-button]:appearance-none",
            withButton && hasValue && "data-active:border-accent pe-12",
          )}
          data-active={withButton && hasValue ? "" : undefined}
        />
        {withButton && hasValue && clearHref ? (
          <Link
            href={clearHref}
            aria-label="Clear search"
            className="rounded-control text-dim hover:text-text absolute end-2 top-1/2 flex size-9 -translate-y-1/2 items-center justify-center transition-[color,scale] duration-300 ease-out hover:scale-110"
          >
            <Icon name="x" className="size-5" />
          </Link>
        ) : null}
      </div>
      {withButton ? (
        <Button type="submit" size="input" className="max-tablet:px-5">
          Search
        </Button>
      ) : null}
    </form>
  );
}
