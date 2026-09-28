"use client";

import { useId } from "react";

/** Sort dropdown that submits the surrounding GET form as soon as it changes (works without JS via the button). */
export function SortSelect({
  query,
  category,
  value,
}: {
  query: string;
  category?: string;
  value: string;
}) {
  const id = useId();

  return (
    <form
      action="/blog/search"
      method="get"
      className="flex items-center gap-3"
    >
      <input type="hidden" name="q" value={query} />
      {category ? (
        <input type="hidden" name="category" value={category} />
      ) : null}
      <label htmlFor={id} className="text-caption text-dim">
        Sort by
      </label>
      <select
        id={id}
        name="sort"
        defaultValue={value}
        onChange={(event) => event.currentTarget.form?.requestSubmit()}
        className="rounded-control border-border-input bg-surface-input text-ui text-text h-10 border px-3"
      >
        <option value="relevance">Relevance</option>
        <option value="newest">Newest</option>
      </select>
      <noscript>
        <button type="submit" className="text-caption text-link">
          Apply
        </button>
      </noscript>
    </form>
  );
}
