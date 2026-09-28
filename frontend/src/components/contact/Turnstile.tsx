"use client";

import { useEffect, useId, useRef } from "react";

declare global {
  interface Window {
    turnstile?: {
      render: (
        element: HTMLElement,
        options: Record<string, unknown>,
      ) => string;
      remove: (widgetId: string) => void;
      reset: (widgetId?: string) => void;
    };
  }
}

const SCRIPT_SRC =
  "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";

function loadScript(): Promise<void> {
  return new Promise((resolve) => {
    if (window.turnstile) return resolve();
    const existing = document.querySelector<HTMLScriptElement>(
      `script[src="${SCRIPT_SRC}"]`,
    );
    const script =
      existing ??
      Object.assign(document.createElement("script"), {
        src: SCRIPT_SRC,
        async: true,
        defer: true,
      });
    script.addEventListener("load", () => resolve(), { once: true });
    if (!existing) document.head.appendChild(script);
  });
}

type TurnstileProps = {
  onVerify: (verified: boolean) => void;
  /** "interaction-only" keeps the widget invisible unless Cloudflare needs a challenge. */
  appearance?: "always" | "interaction-only";
  resetKey?: number;
};

/** Cloudflare Turnstile. The token is submitted in the hidden `cf-turnstile-response` field. */
export function Turnstile({
  onVerify,
  appearance = "always",
  resetKey = 0,
}: TurnstileProps) {
  const container = useRef<HTMLDivElement>(null);
  const id = useId();
  const siteKey = process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY;

  useEffect(() => {
    if (!siteKey || !container.current) return;
    let widgetId: string | undefined;
    let cancelled = false;

    loadScript().then(() => {
      if (cancelled || !container.current || !window.turnstile) return;
      widgetId = window.turnstile.render(container.current, {
        sitekey: siteKey,
        theme: "dark",
        appearance,
        callback: () => onVerify(true),
        "expired-callback": () => onVerify(false),
        "error-callback": () => onVerify(false),
      });
    });

    return () => {
      cancelled = true;
      if (widgetId && window.turnstile) window.turnstile.remove(widgetId);
    };
  }, [siteKey, appearance, onVerify, resetKey]);

  if (!siteKey) return null;

  return <div ref={container} id={id} className="min-h-0" />;
}
