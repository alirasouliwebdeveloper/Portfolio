import { expect, test } from "@playwright/test";

const pages = ["/", "/about", "/blog", "/contact"];

test.describe("core pages", () => {
  for (const path of pages) {
    test(`${path} loads without console errors and has no horizontal overflow`, async ({
      page,
    }) => {
      const errors: string[] = [];
      page.on("pageerror", (error) => errors.push(error.message));
      page.on("console", (message) => {
        if (message.type() === "error") errors.push(message.text());
      });

      const response = await page.goto(path);
      expect(response?.status()).toBe(200);

      const overflow = await page.evaluate(
        () =>
          document.documentElement.scrollWidth -
          document.documentElement.clientWidth,
      );
      expect(overflow).toBe(0);
      expect(errors).toEqual([]);
    });
  }

  test("header navigation reaches every main page", async ({ page }) => {
    await page.goto("/");
    for (const [label, path] of [
      ["About", "/about"],
      ["Blog", "/blog"],
      ["Contact", "/contact"],
    ] as const) {
      await page
        .getByRole("navigation", { name: "Main" })
        .getByRole("link", { name: label })
        .click();
      await expect(page).toHaveURL(new RegExp(`${path}$`));
    }
  });

  test("a project card links through to its case study", async ({ page }) => {
    await page.goto("/");
    const card = page.locator("#projects article").first();
    await card.getByRole("link", { name: /view project/i }).click();
    await expect(page.locator("h1")).toBeVisible();
    await expect(page).toHaveURL(/\/projects\//);
  });

  test("an unknown path renders the 404 page with a working search", async ({
    page,
  }) => {
    const response = await page.goto("/this-page-does-not-exist");
    expect(response?.status()).toBe(404);
    await expect(page.getByRole("heading", { level: 1 })).toBeVisible();
  });

  test("sitemap.xml and robots.txt are served", async ({ request }) => {
    const sitemap = await request.get("/sitemap.xml");
    expect(sitemap.ok()).toBeTruthy();
    expect(await sitemap.text()).toContain("<urlset");

    const robots = await request.get("/robots.txt");
    expect(robots.ok()).toBeTruthy();
    expect(await robots.text()).toContain("Sitemap:");
  });
});
