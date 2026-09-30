"use client";

import { useId, type InputHTMLAttributes } from "react";
import { cn } from "@/lib/cn";
import { FieldError } from "./FormField";

type RadioPillProps = Omit<
  InputHTMLAttributes<HTMLInputElement>,
  "type" | "className"
> & {
  label: string;
};

export function RadioPill({ label, ...props }: RadioPillProps) {
  return (
    <label
      className={cn(
        "rounded-control border-border-input bg-surface-input text-ui text-code flex h-12 cursor-pointer items-center gap-2.5 border px-4 transition-[border-color,background-color] duration-300 ease-out",
        "has-checked:border-accent has-checked:bg-chip has-focus-visible:outline-accent hover:border-accent-border has-focus-visible:outline-2 has-focus-visible:outline-offset-2",
      )}
    >
      <input type="radio" className="accent-accent m-0 size-4.5" {...props} />
      {label}
    </label>
  );
}

type RadioPillGroupProps = {
  legend: string;
  name: string;
  options: readonly string[];
  value?: string;
  defaultValue?: string;
  onChange?: (value: string) => void;
  error?: string;
  className?: string;
};

export function RadioPillGroup({
  legend,
  name,
  options,
  value,
  defaultValue,
  onChange,
  error,
  className,
}: RadioPillGroupProps) {
  const errorId = `${useId()}-error`;
  return (
    <fieldset
      className={cn("m-0 border-0 p-0", className)}
      aria-describedby={error ? errorId : undefined}
    >
      <legend className="text-text-2 mb-3 text-sm font-medium">{legend}</legend>
      <div className="flex flex-wrap gap-3">
        {options.map((option) => (
          <RadioPill
            key={option}
            name={name}
            label={option}
            value={option}
            {...(value !== undefined
              ? {
                  checked: value === option,
                  onChange: () => onChange?.(option),
                }
              : {
                  defaultChecked: defaultValue === option,
                  onChange: () => onChange?.(option),
                })}
          />
        ))}
      </div>
      {error ? (
        <div className="mt-2">
          <FieldError id={errorId}>{error}</FieldError>
        </div>
      ) : null}
    </fieldset>
  );
}
