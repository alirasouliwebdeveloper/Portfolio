const dateFormat = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  // Publish times are entered in Tehran time in the admin panel; show the same calendar day.
  timeZone: "Asia/Tehran",
});

/** "Sep 22, 2026" */
export const formatDate = (iso: string) => dateFormat.format(new Date(iso));

/** "9 min read" */
export const formatReadingTime = (minutes: number) => `${minutes} min read`;

/** "Sep 22, 2026 · 9 min read" */
export const formatPostMeta = (iso: string, minutes: number) =>
  `${formatDate(iso)} · ${formatReadingTime(minutes)}`;
