"use client";

import {
  useEffect,
  useId,
  useRef,
  useState,
  type KeyboardEvent as ReactKeyboardEvent,
} from "react";
import { cn } from "@/lib/cn";
import { ChevronDown, Icon } from "./icons";

type ListboxProps = {
  options: readonly string[];
  value?: string;
  defaultValue?: string;
  onChange?: (value: string) => void;
  name?: string;
  id?: string;
  placeholder?: string;
  disabled?: boolean;
  triggerClassName?: string;
  panelClassName?: string;
  "aria-invalid"?: boolean | "true" | "false";
  "aria-describedby"?: string;
  "aria-label"?: string;
  "aria-labelledby"?: string;
};

const focusOption = (list: HTMLUListElement | null, index: number) =>
  (list?.children[index] as HTMLElement | undefined)?.focus();

/**
 * Themed stand-in for a native `<select>`: once opened, browsers render the option list with
 * unstyleable OS chrome, so this implements the WAI-ARIA listbox pattern by hand instead
 * (https://www.w3.org/WAI/ARIA/apg/patterns/listbox/examples/listbox-collapsible/). Posts like a
 * real form field via a hidden input when `name` is set.
 */
export function Listbox({
  options,
  value,
  defaultValue,
  onChange,
  name,
  id,
  placeholder,
  disabled,
  triggerClassName,
  panelClassName,
  ...aria
}: ListboxProps) {
  const [open, setOpen] = useState(false);
  const [internal, setInternal] = useState(defaultValue ?? options[0] ?? "");
  const selected = value ?? internal;
  const [activeIndex, setActiveIndex] = useState(() =>
    Math.max(0, options.indexOf(selected)),
  );
  const root = useRef<HTMLDivElement>(null);
  const list = useRef<HTMLUListElement>(null);
  const generatedId = useId();
  const baseId = id ?? generatedId;
  const listId = `${baseId}-listbox`;

  const commit = (next: string) => {
    if (value === undefined) setInternal(next);
    onChange?.(next);
  };

  const close = (refocusTrigger: boolean) => {
    setOpen(false);
    if (refocusTrigger) root.current?.querySelector("button")?.focus();
  };

  // Sets the index the panel should open with; the effect below only handles focusing it.
  const openAt = (index: number) => {
    setActiveIndex(index);
    setOpen(true);
  };

  // Close on an outside click; the panel itself is inside `root`, so this only catches the rest.
  useEffect(() => {
    if (!open) return;
    const onPointerDown = (event: PointerEvent) => {
      if (root.current && !root.current.contains(event.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener("pointerdown", onPointerDown);
    return () => document.removeEventListener("pointerdown", onPointerDown);
  }, [open]);

  // Opening focuses the active option so arrow keys work immediately, matching a native select.
  useEffect(() => {
    if (!open) return;
    const frame = requestAnimationFrame(() =>
      focusOption(list.current, activeIndex),
    );
    return () => cancelAnimationFrame(frame);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  const move = (delta: number) => {
    const next = Math.min(options.length - 1, Math.max(0, activeIndex + delta));
    setActiveIndex(next);
    focusOption(list.current, next);
  };

  const onOptionKeyDown = (
    event: ReactKeyboardEvent<HTMLLIElement>,
    option: string,
  ) => {
    switch (event.key) {
      case "ArrowDown":
        event.preventDefault();
        move(1);
        break;
      case "ArrowUp":
        event.preventDefault();
        move(-1);
        break;
      case "Home":
        event.preventDefault();
        setActiveIndex(0);
        focusOption(list.current, 0);
        break;
      case "End":
        event.preventDefault();
        setActiveIndex(options.length - 1);
        focusOption(list.current, options.length - 1);
        break;
      case "Enter":
      case " ":
        event.preventDefault();
        commit(option);
        close(true);
        break;
      case "Escape":
        event.preventDefault();
        close(true);
        break;
      case "Tab":
        setOpen(false);
        break;
    }
  };

  return (
    <div ref={root} className="relative">
      {name ? <input type="hidden" name={name} value={selected} /> : null}
      <button
        type="button"
        id={baseId}
        disabled={disabled}
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-controls={open ? listId : undefined}
        {...aria}
        onClick={() => {
          if (disabled) return;
          if (open) setOpen(false);
          else openAt(Math.max(0, options.indexOf(selected)));
        }}
        onKeyDown={(event) => {
          if (disabled) return;
          if (["ArrowDown", "ArrowUp", "Enter", " "].includes(event.key)) {
            event.preventDefault();
            openAt(Math.max(0, options.indexOf(selected)));
          }
        }}
        className={cn(
          "flex w-full items-center justify-between gap-2 text-start disabled:cursor-not-allowed disabled:opacity-50",
          triggerClassName,
        )}
      >
        <span className={cn("truncate", !selected && "text-dim")}>
          {selected || placeholder}
        </span>
        <ChevronDown
          className={cn(
            "text-dim size-4 shrink-0 transition-transform duration-150",
            open && "rotate-180",
          )}
        />
      </button>

      {open ? (
        <ul
          ref={list}
          id={listId}
          role="listbox"
          tabIndex={-1}
          aria-activedescendant={`${listId}-${activeIndex}`}
          className={cn(
            "border-border-input bg-surface-strong shadow-float rounded-control absolute inset-x-0 top-[calc(100%+0.5rem)] z-20 max-h-64 overflow-auto border p-1.5",
            panelClassName,
          )}
        >
          {options.map((option, index) => {
            const isSelected = option === selected;
            return (
              <li
                key={option}
                id={`${listId}-${index}`}
                role="option"
                aria-selected={isSelected}
                tabIndex={index === activeIndex ? 0 : -1}
                onMouseEnter={() => setActiveIndex(index)}
                onClick={() => {
                  commit(option);
                  close(true);
                }}
                onKeyDown={(event) => onOptionKeyDown(event, option)}
                className={cn(
                  "text-ui rounded-control flex cursor-pointer items-center justify-between gap-2 px-3.5 py-2.5 outline-none",
                  isSelected
                    ? "bg-chip text-link font-medium"
                    : "text-text-2 hover:bg-chip/60",
                  index === activeIndex && !isSelected && "bg-chip/60",
                )}
              >
                {option}
                {isSelected ? (
                  <Icon name="check" className="size-4 shrink-0" />
                ) : null}
              </li>
            );
          })}
        </ul>
      ) : null}
    </div>
  );
}
