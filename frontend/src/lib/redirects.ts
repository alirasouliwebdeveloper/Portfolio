import "server-only";

import { permanentRedirect } from "next/navigation";
import { getRedirect } from "./api";

/** Call before `notFound()`: an old URL whose slug changed is sent to its new home. */
export async function redirectIfMoved(path: string) {
  const to = await getRedirect(path);
  if (to) permanentRedirect(to);
}
