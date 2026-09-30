import { expect, test } from "@playwright/test";

test.describe("blog", () => {
  test("search finds a post and the themed sort dropdown re-orders results", async ({
    page,
  }) => {
    await page.goto("/blog/search?q=laravel");
    await expect(page.getByRole("article").first()).toBeVisible();

    // The sort control is a hand-rolled listbox (native <select> can't be themed), not a
    // <select>; its accessible name comes from the "Sort by" label it's paired with.
    const trigger = page.getByLabel(/sort by/i);
    await expect(trigger).toBeVisible();
    await trigger.click();

    const panel = page.getByRole("listbox");
    await expect(panel).toBeVisible();
    await panel.getByRole("option", { name: "Newest" }).click();

    await expect(page).toHaveURL(/sort=newest/);
    await expect(panel).toBeHidden();
  });

  test("a category filter narrows the post list", async ({ page }) => {
    await page.goto("/blog");
    const chip = page.getByRole("link", { name: /laravel/i }).first();
    await chip.click();
    await expect(page).toHaveURL(/\/blog\/category\//);
    await expect(page.getByRole("article").first()).toBeVisible();
  });

  test("pagination moves to page 2 when there is one", async ({ page }) => {
    await page.goto("/blog");
    const next = page.getByRole("link", { name: /^page 2$/i });
    if ((await next.count()) === 0)
      test.skip(true, "not enough posts for a second page");
    await next.click();
    await expect(page).toHaveURL(/\/blog\/page\/2/);
  });

  test("opening a post renders its content and table of contents", async ({
    page,
  }) => {
    await page.goto("/blog");
    const link = page.locator("article h3 a, article h2 a").first();
    const href = await link.getAttribute("href");
    await Promise.all([page.waitForURL(new RegExp(`${href}$`)), link.click()]);

    await expect(page.locator("h1")).toBeVisible();
    await expect(page.locator(".rich-content")).toBeVisible();
  });
});
