"use client";

import { useEffect, useRef, useState, type ReactNode } from "react";
import { usePathname } from "next/navigation";
import { Button } from "@/components/ui/Button";
import { Icon } from "@/components/ui/icons";
import { NavLinks } from "./NavLinks";

/** Full-screen menu for tablet and mobile: focus is trapped, Esc and route changes close it. */
export function MobileMenu({
  logo,
  socials,
}: {
  logo: ReactNode;
  socials: ReactNode;
}) {
  const pathname = usePathname();
  // The menu belongs to the page it was opened on: navigating elsewhere closes it.
  const [state, setState] = useState({ open: false, path: pathname });
  const open = state.open && state.path === pathname;
  const setOpen = (value: boolean) => setState({ open: value, path: pathname });
  const panel = useRef<HTMLDivElement>(null);
  const trigger = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (!open) return;

    const previous = trigger.current;
    document.body.style.overflow = "hidden";
    panel.current?.querySelector<HTMLElement>("a, button")?.focus();

    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setState((current) => ({ ...current, open: false }));
        return;
      }
      if (event.key !== "Tab" || !panel.current) return;

      const focusable = panel.current.querySelectorAll<HTMLElement>(
        "a[href], button:not([disabled])",
      );
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };

    document.addEventListener("keydown", onKey);
    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = "";
      previous?.focus();
    };
  }, [open]);

  return (
    <>
      <button
        ref={trigger}
        type="button"
        aria-label="Open menu"
        aria-expanded={open}
        aria-controls="mobile-menu"
        onClick={() => setOpen(true)}
        className="rounded-card border-border-input bg-surface text-text desktop:hidden flex size-11.5 items-center justify-center border"
      >
        <Icon name="menu" className="size-5" />
      </button>

      {open ? (
        <div
          id="mobile-menu"
          ref={panel}
          role="dialog"
          aria-modal="true"
          aria-label="Menu"
          className="bg-bg px-gutter desktop:hidden fixed inset-0 z-50 flex flex-col overflow-y-auto"
        >
          <div className="h-header flex shrink-0 items-center justify-between">
            {logo}
            <button
              type="button"
              aria-label="Close menu"
              onClick={() => setOpen(false)}
              className="rounded-card border-border-input bg-surface text-text flex size-11.5 items-center justify-center border"
            >
              <Icon name="x" className="size-5" />
            </button>
          </div>

          <NavLinks
            className="mt-8"
            linkClassName="text-[2rem] font-semibold leading-tight py-3"
          />

          <div className="mt-10 flex flex-col gap-8 pb-10">
            <Button href="/contact" fullWidth>
              Hire Me
            </Button>
            {socials}
          </div>
        </div>
      ) : null}
    </>
  );
}
