// Crawls the running site from the sitemap and reports broken internal links, missing
// titles/descriptions/canonicals and pages without exactly one <h1>.
// Usage: SITE_URL=http://localhost:3000 node scripts/check-links.mjs
const site = (process.env.SITE_URL ?? "http://localhost:3000").replace(
  /\/$/,
  "",
);

const get = (url) => fetch(url, { redirect: "manual" });
const sitemap = await (await get(`${site}/sitemap.xml`)).text();
const queue = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) =>
  m[1].replace(/^https?:\/\/[^/]+/, site),
);
const seen = new Set();
const problems = [];

while (queue.length) {
  const url = queue.shift().split("#")[0];
  if (seen.has(url)) continue;
  seen.add(url);

  const response = await get(url);
  if (response.status >= 300 && response.status < 400) {
    problems.push(
      `${url} redirects (${response.status}) to ${response.headers.get("location")}`,
    );
    continue;
  }
  if (response.status !== 200) {
    problems.push(`${url} -> ${response.status}`);
    continue;
  }
  if (!(response.headers.get("content-type") ?? "").includes("text/html"))
    continue;

  const html = await response.text();
  const check = (ok, message) => ok || problems.push(`${url}: ${message}`);
  check(/<title>[^<]{10,}<\/title>/.test(html), "missing or short <title>");
  check(
    /<meta name="description" content="[^"]{50,}/.test(html),
    "missing or short meta description",
  );
  check(/<link rel="canonical"/.test(html), "missing canonical");
  check(/<meta property="og:title"/.test(html), "missing og:title");
  check(
    (html.match(/<h1[\s>]/g) ?? []).length === 1,
    "expected exactly one <h1>",
  );
  check(!/<img(?![^>]*\balt=)[^>]*>/.test(html), "image without alt attribute");

  for (const [, href] of html.matchAll(/<a [^>]*href="(\/[^"#]*)[^"]*"/g)) {
    if (!href.startsWith("//")) queue.push(site + href);
  }
}

console.log(`Checked ${seen.size} URLs.`);
if (problems.length) {
  console.log(
    `\n${problems.length} problem(s):\n- ${[...new Set(problems)].join("\n- ")}`,
  );
  process.exit(1);
}
console.log("No broken links or missing metadata.");
