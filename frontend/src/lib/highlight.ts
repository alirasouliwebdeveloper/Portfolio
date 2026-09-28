import "server-only";

import { codeToHtml } from "shiki";

const entities: Record<string, string> = {
  "&lt;": "<",
  "&gt;": ">",
  "&amp;": "&",
  "&quot;": '"',
  "&#39;": "'",
  "&#x27;": "'",
};
const decode = (text: string) =>
  text.replace(
    /&(lt|gt|amp|quot|#39|#x27);/g,
    (match) => entities[match] ?? match,
  );

const aliases: Record<string, string> = {
  sh: "bash",
  shell: "bash",
  yml: "yaml",
  js: "javascript",
  ts: "typescript",
  dockerfile: "docker",
  "": "text",
};

/**
 * Server-side syntax highlighting for `<pre><code class="language-x">` blocks in article HTML.
 * Runs at render time on the server, so no highlighter ships to the browser.
 */
export async function highlightCode(html: string): Promise<string> {
  const pattern =
    /<pre>\s*<code(?:\s+class="language-([\w+#.-]+)")?[^>]*>([\s\S]*?)<\/code>\s*<\/pre>/g;
  const matches = [...html.matchAll(pattern)];
  if (matches.length === 0) return html;

  const replacements = await Promise.all(
    matches.map(async (match) => {
      const requested = (match[1] ?? "").toLowerCase();
      const lang = aliases[requested] ?? requested;
      try {
        return await codeToHtml(decode(match[2]), {
          lang: lang || "text",
          theme: "github-dark-default",
        });
      } catch {
        // Unknown language: fall back to the plain block.
        return match[0];
      }
    }),
  );

  let index = 0;
  return html.replace(pattern, () => replacements[index++]);
}
