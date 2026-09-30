import type { Instrumentation } from "next";

/**
 * Server-side error monitoring (Sentry). No-ops entirely when SENTRY_DSN isn't set — nothing is
 * sent, no account required. See docs/02-architecture.md and .env.example.
 */
export async function register() {
  if (!process.env.SENTRY_DSN) return;

  const Sentry = await import("@sentry/nextjs");
  Sentry.init({
    dsn: process.env.SENTRY_DSN,
    environment: process.env.SENTRY_ENVIRONMENT ?? process.env.NODE_ENV,
    tracesSampleRate: Number(process.env.SENTRY_TRACES_SAMPLE_RATE ?? 0.2),
  });
}

export const onRequestError: Instrumentation.onRequestError = async (
  ...args
) => {
  if (!process.env.SENTRY_DSN) return;

  const Sentry = await import("@sentry/nextjs");
  await Sentry.captureRequestError(...args);
};
