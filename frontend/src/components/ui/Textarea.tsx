import { useId, type TextareaHTMLAttributes } from "react";
import { cn } from "@/lib/cn";
import { controlClasses, describedBy, FormField } from "./FormField";

type TextareaProps = Omit<
  TextareaHTMLAttributes<HTMLTextAreaElement>,
  "className"
> & {
  label: string;
  optional?: boolean;
  hint?: string;
  error?: string;
  className?: string;
};

export function Textarea({
  label,
  optional,
  hint,
  error,
  className,
  id,
  ...props
}: TextareaProps) {
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
      <textarea
        id={fieldId}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(fieldId, hint, error)}
        className={cn(
          controlClasses,
          "min-h-40 resize-y py-3.5 leading-relaxed",
        )}
        {...props}
      />
    </FormField>
  );
}
