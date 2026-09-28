import { useId, type SelectHTMLAttributes } from "react";
import { cn } from "@/lib/cn";
import { controlClasses, describedBy, FormField } from "./FormField";
import { ChevronDown } from "./icons";

type SelectProps = Omit<
  SelectHTMLAttributes<HTMLSelectElement>,
  "className"
> & {
  label: string;
  options: readonly string[];
  optional?: boolean;
  hint?: string;
  error?: string;
  className?: string;
};

export function Select({
  label,
  options,
  optional,
  hint,
  error,
  className,
  id,
  ...props
}: SelectProps) {
  const generated = useId();
  const fieldId = id ?? generated;
  return (
    <FormField
      id={fieldId}
      label={label}
      optional={optional}
      hint={hint}
      error={error}
      className={className}
    >
      <div className="relative">
        <select
          id={fieldId}
          aria-invalid={error ? true : undefined}
          aria-describedby={describedBy(fieldId, hint, error)}
          className={cn(controlClasses, "h-12.5 appearance-none pe-11")}
          {...props}
        >
          {options.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
        <ChevronDown className="text-dim pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2" />
      </div>
    </FormField>
  );
}
