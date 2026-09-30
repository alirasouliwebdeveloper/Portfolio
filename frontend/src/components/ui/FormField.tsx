import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Icon } from "./icons";

export const controlClasses = cn(
  "w-full rounded-control border border-border-input bg-surface-input px-4 text-ui text-text transition-colors duration-300 ease-out",
  "placeholder:text-dim hover:border-accent-border focus-visible:border-accent",
  "aria-invalid:border-danger-border",
);

export function FieldError({
  id,
  children,
}: {
  id?: string;
  children: ReactNode;
}) {
  return (
    <p
      id={id}
      role="alert"
      className="text-caption text-danger flex items-start gap-2"
    >
      <Icon name="alert" className="mt-0.5 size-4 shrink-0" />
      <span>{children}</span>
    </p>
  );
}

type FormFieldProps = {
  id: string;
  label: string;
  optional?: boolean;
  hint?: string;
  error?: string;
  className?: string;
  children: ReactNode;
};

export function FormField({
  id,
  label,
  optional,
  hint,
  error,
  className,
  children,
}: FormFieldProps) {
  return (
    <div className={cn("flex flex-col gap-2", className)}>
      <label htmlFor={id} className="text-text-2 text-sm font-medium">
        {label}
        {optional ? (
          <span className="text-dim font-normal"> (optional)</span>
        ) : null}
      </label>
      {children}
      {hint && !error ? (
        <p id={`${id}-hint`} className="text-caption text-dim">
          {hint}
        </p>
      ) : null}
      {error ? <FieldError id={`${id}-error`}>{error}</FieldError> : null}
    </div>
  );
}

export function describedBy(id: string, hint?: string, error?: string) {
  if (error) return `${id}-error`;
  if (hint) return `${id}-hint`;
  return undefined;
}
