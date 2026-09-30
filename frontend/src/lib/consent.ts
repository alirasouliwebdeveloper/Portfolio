"use client";

import { useSyncExternalStore } from "react";

/**
 * A minimal cookie-consent flag for GA4 (the only tracking script this site loads). Stored in
 * localStorage — a per-browser convenience, not synced anywhere — and wrapped in try/catch since
 * it can throw (private browsing, blocked storage) and must never break the page either way.
 */
const KEY = "cookie-consent";
const EVENT = "cookie-consent-changed";

export type ConsentChoice = "accepted" | "declined";

export function getConsentChoice(): ConsentChoice | null {
  try {
    const value = localStorage.getItem(KEY);
    return value === "accepted" || value === "declined" ? value : null;
  } catch {
    return null;
  }
}

export function setConsentChoice(choice: ConsentChoice) {
  try {
    localStorage.setItem(KEY, choice);
  } catch {
    // Ignore — the banner still closes for this page view even if it can't remember the choice.
  }
  window.dispatchEvent(new Event(EVENT));
}

export const hasAnalyticsConsent = () => getConsentChoice() === "accepted";

/** Calls `callback` whenever the choice changes (this tab only). Returns an unsubscribe function. */
export function onConsentChange(callback: () => void) {
  window.addEventListener(EVENT, callback);
  return () => window.removeEventListener(EVENT, callback);
}

// Reading localStorage during the initial render would mismatch the server-rendered HTML (it
// doesn't exist there), so this reads it through useSyncExternalStore — React's own API for
// syncing to a browser-only source, which knows to use `null` for the server/first-paint render
// and only switch to the real value once hydration has finished.
const subscribe = (callback: () => void) => onConsentChange(callback);
const getServerSnapshot = () => null;

/** The visitor's current consent choice, `null` until they've decided (or before hydration). */
export function useConsentChoice(): ConsentChoice | null {
  return useSyncExternalStore(subscribe, getConsentChoice, getServerSnapshot);
}
