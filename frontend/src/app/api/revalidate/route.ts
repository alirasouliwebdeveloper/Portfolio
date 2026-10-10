import { timingSafeEqual } from "node:crypto";
import { revalidatePath, revalidateTag } from "next/cache";
import { NextResponse, type NextRequest } from "next/server";

const TAG = /^[a-z0-9:_-]{1,120}$/i;
const MAX_TAGS = 50;

/** `kind:slug` tags sent by Laravel → the public path of that record. */
const ENTITY_PATHS: Record<string, string> = {
  post: "/blog",
  project: "/projects",
  service: "/services",
  category: "/blog/category",
};

function authorized(request: NextRequest) {
  const secret = process.env.REVALIDATE_SECRET;
  if (!secret) return false;
  const header = request.headers.get("authorization") ?? "";
  const given = Buffer.from(header.replace(/^Bearer\s+/i, ""));
  const expected = Buffer.from(secret);
  return given.length === expected.length && timingSafeEqual(given, expected);
}

/** Laravel calls this after content changes with the cache tags to refresh (docs/02-architecture.md). */
export async function POST(request: NextRequest) {
  if (!authorized(request)) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }

  const body = (await request.json().catch(() => null)) as {
    tags?: unknown;
  } | null;
  const tags = Array.isArray(body?.tags)
    ? [...new Set(body.tags)]
        .filter(
          (tag): tag is string => typeof tag === "string" && TAG.test(tag),
        )
        .slice(0, MAX_TAGS)
    : [];

  if (tags.length === 0) {
    return NextResponse.json({ message: "No valid tags" }, { status: 422 });
  }

  // expire: 0 → the next visit is a blocking fresh render, so an edit shows up immediately.
  for (const tag of tags) revalidateTag(tag, { expire: 0 });

  // A page cached as 404 before its content was published must go too, whatever tags it recorded.
  for (const tag of tags) {
    const [kind, slug] = tag.split(":");
    const base = slug ? ENTITY_PATHS[kind] : undefined;
    if (base) revalidatePath(`${base}/${slug}`, "layout");
  }

  // "all" is sent after a deploy: the build was pre-rendered in CI, so every page is refreshed.
  if (tags.includes("all")) revalidatePath("/", "layout");

  return NextResponse.json({ revalidated: tags });
}
