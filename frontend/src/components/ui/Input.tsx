import { useId, type InputHTMLAttributes } from "react";
import { cn } from "@/lib/cn";
import { controlClasses, describedBy, FormField } from "./FormField";

type InputProps = Omit<InputHTMLAttributes<HTMLInputElement>, "className"> & {
  label: string;
  optional?: boolean;
  hint?: string;
  error?: string;
  className?: string;
};

export function Input({
  label,
  optional,
  hint,
  error,
  className,
  id,
  ...props
}: InputProps) {
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
      <input
        id={fieldId}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(fieldId, hint, error)}
        className={cn(controlClasses, "h-12.5")}
        {...props}
      />
    </FormField>
  );
}
