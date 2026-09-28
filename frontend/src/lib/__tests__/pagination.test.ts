import { describe, expect, it } from "vitest";
import { paginationItems } from "@/components/ui/Pagination";

describe("paginationItems", () => {
  it("lists every page when there are 5 or fewer", () => {
    expect(paginationItems(1, 1)).toEqual([1]);
    expect(paginationItems(3, 5)).toEqual([1, 2, 3, 4, 5]);
  });

  it("shows the first three pages, an ellipsis and the last page at the start", () => {
    expect(paginationItems(1, 8)).toEqual([1, 2, 3, "gap", 8]);
    expect(paginationItems(2, 8)).toEqual([1, 2, 3, "gap", 8]);
  });

  it("shows a window around the current page in the middle", () => {
    expect(paginationItems(4, 8)).toEqual([1, "gap", 3, 4, 5, "gap", 8]);
    expect(paginationItems(5, 9)).toEqual([1, "gap", 4, 5, 6, "gap", 9]);
  });

  it("does not add an ellipsis next to the first page when the window touches it", () => {
    expect(paginationItems(3, 6)).toEqual([1, 2, 3, 4, "gap", 6]);
    expect(paginationItems(3, 8)).toEqual([1, 2, 3, 4, "gap", 8]);
  });

  it("shows the last three pages and the first page at the end", () => {
    expect(paginationItems(8, 8)).toEqual([1, "gap", 6, 7, 8]);
    expect(paginationItems(7, 8)).toEqual([1, "gap", 6, 7, 8]);
  });

  it("never repeats or skips a page number", () => {
    for (let total = 1; total <= 20; total++) {
      for (let current = 1; current <= total; current++) {
        const numbers = paginationItems(current, total).filter(
          (item): item is number => item !== "gap",
        );
        expect(new Set(numbers).size).toBe(numbers.length);
        expect(numbers).toContain(current);
        expect(numbers).toContain(1);
        expect(numbers).toContain(total);
      }
    }
  });
});
