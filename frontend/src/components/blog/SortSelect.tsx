"use client";

import { useId, useRef } from "react";
import { Listbox } from "@/components/ui/Listbox";

const labels = { relevance: "Relevance", newest: "Newest" } as const;
const options = Object.values(labels);
const keyFor = (label: string) =>
  (Object.keys(labels) as (keyof typeof labels)[]).find(
    (key) => labels[key] === label,
  ) ?? "relevance";

/** Sort dropdown that submits the surrounding GET form as soon as a choice is made. */
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
  const form = useRef<HTMLFormElement>(null);
  const sortInput = useRef<HTMLInputElement>(null);

  return (
    <form
      ref={form}
      action="/blog/search"
      method="get"
      className="flex items-center gap-3"
    >
      <input type="hidden" name="q" value={query} />
      {category ? (
        <input type="hidden" name="category" value={category} />
      ) : null}
      <input ref={sortInput} type="hidden" name="sort" defaultValue={value} />
      <label htmlFor={id} className="text-caption text-dim shrink-0">
        Sort by
      </label>
      <Listbox
        id={id}
        options={options}
        defaultValue={labels[value as keyof typeof labels] ?? labels.relevance}
        onChange={(label) => {
          // Written straight to the DOM (not React state) so the value is current before submit.
          if (sortInput.current) sortInput.current.value = keyFor(label);
          form.current?.requestSubmit();
        }}
        triggerClassName="rounded-control border-border-input bg-surface-input text-ui text-text h-10 border px-3 w-44"
      />
    </form>
  );
}
