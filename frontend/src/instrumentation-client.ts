/**
 * Browser-side error monitoring (Sentry). No-ops entirely when NEXT_PUBLIC_SENTRY_DSN isn't set
 * at build time — nothing is sent, no account required. See docs/02-architecture.md.
 */
if (process.env.NEXT_PUBLIC_SENTRY_DSN) {
  void import("@sentry/nextjs").then((Sentry) => {
    Sentry.init({
      dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
      environment:
        process.env.NEXT_PUBLIC_SENTRY_ENVIRONMENT ?? process.env.NODE_ENV,
      tracesSampleRate: 0.2,
    });
  });
}
