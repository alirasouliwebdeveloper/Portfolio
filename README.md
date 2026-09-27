# Ali Portfolio — Claude Code handoff

Everything Claude Code needs to build the site from the finished design.

## How to use
1. Create an empty folder for the project, `git init`, and copy the **contents** of this package into it (`CLAUDE.md` must sit at the repo root).
2. Open the folder in a terminal and start Claude Code.
3. Paste the kickoff prompt below.
4. Review each phase summary before letting it continue. Answer its questions — it's instructed to ask instead of guessing on architecture.

## Kickoff prompt
```
Read CLAUDE.md, then every file in docs/ in order, then look at design/screens/ (start with Main.png, Main-Tablet.png, Main-Mobile.png) and seed/mock-data.json.
Before writing code: give me a short plan for Phase 0 and Phase 1, list the exact versions of Laravel, Filament, Next.js and Tailwind you'll install (check the official docs for the current stable releases), and ask me anything that's unclear.
Then start Phase 0.
```

## What's inside
| Path | Contents |
|---|---|
| `CLAUDE.md` | Project rules Claude Code reads automatically |
| `docs/01-product-spec.md` | Every page, route, section and behaviour |
| `docs/02-architecture.md` | Data model, API, uploads/contact, revalidation, frontend structure, Docker |
| `docs/03-design-system.md` | Colors, type scale, spacing, components |
| `docs/04-implementation-plan.md` | 9 phases with checks |
| `docs/05-seo-and-content.md` | Metadata, JSON-LD, sitemap, internal linking |
| `design/screens/` | Full-page PNGs of all 38 artboards (desktop 1920, `-Tablet` 834, `-Mobile` 390) |
| `design/html/` | Static HTML of each artboard — open in a browser to inspect exact values |
| `design/tokens.json` | Design tokens |
| `assets/images/` | Portrait + mock screenshots and blog covers |
| `seed/mock-data.json` | Mock content for the database seeder |

## Before launch — replace mock content
Stats, testimonials, project details and results, experience, prices on service pages, FAQ answers, email/phone/city, social links, project screenshots and blog covers are all placeholders. Also decide the final brand name: the logo still reads "CodeCraft" from the reference design.

## Not designed yet (Claude Code will build these from the tokens)
Mobile menu open state, contact success/error states, projects index page, privacy/terms pages.
