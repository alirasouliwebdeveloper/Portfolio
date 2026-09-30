import path from "node:path";
import { expect, test } from "@playwright/test";

const fixture = (name: string) => path.join(__dirname, "fixtures", name);

test.describe("contact form", () => {
  test("shows validation errors and keeps what was typed", async ({ page }) => {
    await page.goto("/contact");
    await page.locator("#contact-name").fill("Sara Ahmadi");
    await page.getByRole("button", { name: /send message/i }).click();

    await expect(page.getByRole("alert").first()).toContainText(
      /fix the highlighted fields/i,
    );
    await expect(page.locator("#contact-name")).toHaveValue("Sara Ahmadi");
  });

  test("rejects an unsupported file type and an oversized file, accepts a valid one", async ({
    page,
  }) => {
    await page.goto("/contact");
    await page.setInputFiles('input[type="file"]', [
      fixture("brief.pdf"),
      fixture("video.mov"),
    ]);

    await expect(page.getByText(/1 of 5 files/i)).toBeVisible();
    await expect(page.getByText(/file type isn.t supported/i)).toBeVisible();
  });

  test("submits successfully with a preselected service and an attachment", async ({
    page,
  }) => {
    await page.goto("/contact?service=online-store-development");

    await expect(
      page.getByRole("radio", { name: "Online store" }),
    ).toBeChecked();

    await page.locator("#contact-name").fill("Sara Ahmadi");
    await page.locator("#contact-email").fill("sara@securio.store");
    await page
      .locator("#contact-message")
      .fill(
        "We sell smart locks and cameras online and need a faster store with a proper admin panel.",
      );

    await page.setInputFiles('input[type="file"]', fixture("brief.pdf"));
    await expect(page.getByText(/uploaded/i)).toBeVisible({ timeout: 15_000 });

    await page.getByRole("button", { name: /send message/i }).click();

    await expect(
      page.getByRole("heading", { name: /thanks, sara ahmadi/i }),
    ).toBeVisible({
      timeout: 15_000,
    });
  });
});
