"use client";

import { Button } from "@/components/ui/Button";
import { setConsentChoice, useConsentChoice } from "@/lib/consent";

/** Bottom banner asking to enable analytics; only rendered when the site actually has GA configured. */
export function CookieConsent({ hasAnalytics }: { hasAnalytics: boolean }) {
  const consent = useConsentChoice();

  if (!hasAnalytics || consent !== null) return null;

  return (
    <div
      role="region"
      aria-label="Cookie notice"
      className="border-border bg-surface-strong shadow-float rounded-card-lg tablet:flex-row tablet:items-center tablet:gap-8 fixed inset-x-4 bottom-4 z-40 mx-auto flex max-w-180 flex-col gap-4 border p-5 tablet:bottom-6"
    >
      <p className="text-ui text-text-2 leading-relaxed">
        This site uses Google Analytics to see which pages are useful. No data is shared beyond
        that, and nothing is loaded unless you accept.{" "}
        <a href="/privacy" className="text-link underline underline-offset-4">
          Privacy policy
        </a>
        .
      </p>
      <div className="flex shrink-0 gap-3">
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={() => setConsentChoice("declined")}
        >
          Decline
        </Button>
        <Button type="button" size="sm" onClick={() => setConsentChoice("accepted")}>
          Accept
        </Button>
      </div>
    </div>
  );
}
