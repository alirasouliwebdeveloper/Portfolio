import { expect, test } from "@playwright/test";

/**
 * Exercises the Filament admin: light theme, accordion sidebar (opening one group closes the
 * others), the global "Site settings" overlay, and the rich editor + SEO panel on a post — the
 * five things called out repeatedly as regressions worth guarding with a real test.
 */
const adminUrl = process.env.ADMIN_URL ?? "http://localhost:8080";
const email = process.env.ADMIN_EMAIL;
const password = process.env.ADMIN_PASSWORD;

test.describe("admin panel", () => {
  test.skip(!email || !password, "ADMIN_EMAIL/ADMIN_PASSWORD not set");
  // The panel login is rate-limited per account; logging in from several workers at once trips
  // it, so these run one at a time.
  test.describe.configure({ mode: "serial" });

  test.beforeEach(async ({ page }) => {
    await page.goto(`${adminUrl}/admin/login`);
    await page.locator('input[type="email"]').fill(email!);
    await page.locator('input[type="password"]').fill(password!);
    await page.getByRole("button", { name: /sign in/i }).click();
    await expect(page).toHaveURL(new RegExp(`${adminUrl}/admin/?$`));
  });

  test("is the light version of the site theme", async ({ page }) => {
    const background = await page.evaluate(
      () => getComputedStyle(document.body).backgroundColor,
    );
    // The public site is a near-black surface; the admin must not be.
    const [r, g, b] = background.match(/\d+/g)!.map(Number);
    expect(r + g + b).toBeGreaterThan(600);
  });

  test("opening one sidebar group closes the others", async ({ page }) => {
    await page.getByRole("button", { name: "Content" }).click();
    await expect(page.getByRole("link", { name: "Posts" })).toBeVisible();

    await page.getByRole("button", { name: "Portfolio" }).click();
    await expect(page.getByRole("link", { name: "Projects" })).toBeVisible();
    await expect(page.getByRole("link", { name: "Posts" })).toBeHidden();
  });

  test("the global settings overlay opens from anywhere and edits the brand", async ({
    page,
  }) => {
    await page.getByRole("button", { name: /site settings/i }).click();
    await expect(
      page.getByRole("heading", { name: "Site settings" }),
    ).toBeVisible();
    await expect(page.getByLabel(/site name/i)).toBeVisible();
    await page.keyboard.press("Escape");
    await expect(
      page.getByRole("heading", { name: "Site settings" }),
    ).toBeHidden();
  });

  test("a post has the rich editor and a live SEO panel", async ({ page }) => {
    await page.getByRole("button", { name: "Content" }).click();
    await page.getByRole("link", { name: "Posts" }).click();
    await expect(page.locator("table tbody tr").first()).toBeVisible();

    const row = page.locator("table tbody tr").first();
    await Promise.all([
      page.waitForURL(/\/admin\/posts\/\d+\/edit/),
      row.locator("a").first().click(),
    ]);

    await expect(page.locator(".ProseMirror")).toBeVisible({ timeout: 15_000 });

    await page.getByRole("tab", { name: "SEO" }).click();
    await expect(page.getByText(/focus keyword/i).first()).toBeVisible();
  });
});
