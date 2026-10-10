// @ts-check
/* eslint-disable @typescript-eslint/no-require-imports -- CommonJS: Next loads the handler with require() */
/**
 * Next's file-system cache, plus a revalidated-tags file on disk.
 *
 * The default handler keeps `revalidateTag()` state only in process memory. On cPanel, Passenger
 * stops the Node app when it is idle (and may run more than one process), so an invalidation sent
 * by Laravel was lost on restart and old pages were served again from disk. Here every
 * invalidation is written to `.next/cache/revalidated-tags.json` and every process reloads that
 * file when it changes, so Next's own staleness checks see it after a restart.
 */
const fs = require("node:fs");
const path = require("node:path");
const FileSystemCache =
  require("next/dist/server/lib/incremental-cache/file-system-cache").default;
const {
  tagsManifest,
} = require("next/dist/server/lib/incremental-cache/tags-manifest.external");

/** Entries older than this are dropped; the API fetch safety net (24h) covers anything older. */
const KEEP_MS = 30 * 24 * 60 * 60 * 1000;

/** @typedef {{ stale?: number, expired?: number }} TagEntry */

class PersistentTagsCache extends FileSystemCache {
  /** @param {any} ctx */
  constructor(ctx) {
    super(ctx);
    // Only Next's own serverDistDir: a process.cwd()-based path makes the standalone file
    // tracer copy the whole project (src, tests, configs) into the deploy package.
    /** @type {string | null} */
    this.manifestPath = ctx.serverDistDir
      ? path.join(ctx.serverDistDir, "..", "cache", "revalidated-tags.json")
      : null;
    this.loadedMtime = 0;
    this.sync();
  }

  /** Merges tag invalidations written by any process (or before a restart) into memory. */
  sync() {
    if (!this.manifestPath) return;
    const file = this.manifestPath;
    try {
      const { mtimeMs } = fs.statSync(/*turbopackIgnore: true*/ file);
      if (mtimeMs === this.loadedMtime) return;
      /** @type {Record<string, TagEntry>} */
      const saved = JSON.parse(
        fs.readFileSync(/*turbopackIgnore: true*/ file, "utf8"),
      );
      for (const [tag, entry] of Object.entries(saved)) {
        tagsManifest.set(tag, newest(tagsManifest.get(tag), entry));
      }
      this.loadedMtime = mtimeMs;
    } catch {
      // No file yet, or a write in progress: keep what is in memory.
    }
  }

  persist() {
    if (!this.manifestPath) return;
    const file = this.manifestPath;
    const cutoff = Date.now() - KEEP_MS;
    /** @type {Record<string, TagEntry>} */
    const data = {};
    for (const [tag, entry] of tagsManifest) {
      if (Math.max(entry.stale ?? 0, entry.expired ?? 0) >= cutoff)
        data[tag] = entry;
    }
    try {
      fs.mkdirSync(/*turbopackIgnore: true*/ path.dirname(file), {
        recursive: true,
      });
      const temp = `${file}.${process.pid}.tmp`;
      fs.writeFileSync(/*turbopackIgnore: true*/ temp, JSON.stringify(data));
      fs.renameSync(/*turbopackIgnore: true*/ temp, file);
      this.loadedMtime = fs.statSync(/*turbopackIgnore: true*/ file).mtimeMs;
    } catch (error) {
      console.error("cache-handler: could not save revalidated tags", error);
    }
  }

  /** @param {any[]} args */
  async get(...args) {
    this.sync();
    return super.get(...args);
  }

  /** @param {string | string[]} tags @param {any} [durations] */
  async revalidateTag(tags, durations) {
    this.sync();
    await super.revalidateTag(tags, durations);
    this.persist();
  }
}

/** @param {TagEntry | undefined} a @param {TagEntry} b @returns {TagEntry} */
function newest(a, b) {
  const pick = (/** @type {"stale" | "expired"} */ key) => {
    const values = [a?.[key], b[key]].filter((v) => typeof v === "number");
    return values.length ? Math.max(...values) : undefined;
  };
  /** @type {TagEntry} */
  const entry = {};
  const stale = pick("stale");
  const expired = pick("expired");
  if (stale !== undefined) entry.stale = stale;
  if (expired !== undefined) entry.expired = expired;
  return entry;
}

module.exports = PersistentTagsCache;
