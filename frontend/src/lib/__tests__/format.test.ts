import { describe, expect, it } from "vitest";
import { formatDate, formatPostMeta, formatReadingTime } from "@/lib/format";

describe("format", () => {
  it("formats dates like the design", () => {
    expect(formatDate("2026-09-22T09:00:00+00:00")).toBe("Sep 22, 2026");
  });

  it("formats reading time and the meta line", () => {
    expect(formatReadingTime(9)).toBe("9 min read");
    expect(formatPostMeta("2026-09-22T09:00:00+00:00", 9)).toBe(
      "Sep 22, 2026 · 9 min read",
    );
  });
});
