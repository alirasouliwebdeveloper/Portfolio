import { Fragment } from "react";

/** Wraps every occurrence of the search words in <mark> (title and excerpt of results). */
export function Highlighted({ text, query }: { text: string; query: string }) {
  const terms = [
    ...new Set(
      query
        .trim()
        .split(/\s+/)
        .filter((term) => term.length >= 2),
    ),
  ];
  if (terms.length === 0) return <>{text}</>;

  const pattern = new RegExp(
    `(${terms.map((term) => term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")).join("|")})`,
    "gi",
  );
  const parts = text.split(pattern);

  return (
    <>
      {parts.map((part, index) =>
        index % 2 === 1 ? (
          <mark
            key={index}
            className="rounded-tag-sm bg-accent/30 text-text px-0.5"
          >
            {part}
          </mark>
        ) : (
          <Fragment key={index}>{part}</Fragment>
        ),
      )}
    </>
  );
}
