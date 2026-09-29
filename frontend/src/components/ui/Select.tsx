import { useId } from "react";
import { cn } from "@/lib/cn";
import { controlClasses, describedBy, FormField } from "./FormField";
import { Listbox } from "./Listbox";

type SelectProps = {
  label: string;
  options: readonly string[];
  name?: string;
  defaultValue?: string;
  value?: string;
  onChange?: (value: string) => void;
  optional?: boolean;
  hint?: string;
  error?: string;
  disabled?: boolean;
  className?: string;
  id?: string;
};

export function Select({
  label,
  options,
  name,
  defaultValue,
  value,
  onChange,
  optional,
  hint,
  error,
  disabled,
  className,
  id,
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
      <Listbox
        id={fieldId}
        name={name}
        options={options}
        defaultValue={defaultValue}
        value={value}
        onChange={onChange}
        disabled={disabled}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(fieldId, hint, error)}
        triggerClassName={cn(controlClasses, "h-12.5 pe-4")}
      />
    </FormField>
  );
}
