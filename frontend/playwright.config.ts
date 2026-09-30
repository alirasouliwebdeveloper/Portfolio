import { defineConfig, devices } from "@playwright/test";

/**
 * E2E suite: runs against a already-running site + API (see CI: `.github/workflows/ci.yml`,
 * job `e2e`; locally, `npm run e2e` after `docker compose up -d` and `npm run build && npm start`).
 * Env: BASE_URL, API_BASE_URL, API_INTERNAL_KEY, ADMIN_URL, ADMIN_EMAIL, ADMIN_PASSWORD.
 */
export default defineConfig({
  testDir: "./tests/e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 2 : undefined,
  reporter: process.env.CI ? [["html", { open: "never" }], ["github"]] : "list",
  timeout: 30_000,
  use: {
    baseURL: process.env.BASE_URL ?? "http://localhost:3000",
    trace: "on-first-retry",
    screenshot: "only-on-failure",
  },
  projects: [
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        // CI downloads Playwright's own Chromium (`playwright install`). Locally, sandboxes that
        // can't `sudo` for that installer can point this at a system Chrome instead.
        channel: process.env.PLAYWRIGHT_CHANNEL,
      },
    },
  ],
});
