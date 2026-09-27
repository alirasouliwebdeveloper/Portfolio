# 04 — Implementation plan

Work phase by phase. At the end of each phase: run the checks, fix failures, and write a short summary (what was built, what's left, any decision you need from Ali). Don't start the next phase with failing checks.

## Phase 0 — Scaffold
- Monorepo layout from `CLAUDE.md`, Docker Compose (api, nginx, queue, scheduler, mysql, redis, web), `.env.example` for both apps.
- Laravel installed, Filament installed with an admin user seeder, Pest + Pint configured.
- Next.js (App Router, TS strict, ESLint, Prettier, Tailwind), `next/font` Inter + JetBrains Mono.
- **Check:** `docker compose up` serves `localhost:3000` (Next) and `localhost:8000/admin` (Filament login).

## Phase 1 — Design system
- Tokens from `design/tokens.json` → CSS variables + Tailwind theme. Logical-property utilities only.
- `ui/` components: Button (primary/outline, sizes, full-width), Container, Section (bg variants), Eyebrow, SectionHeading, IconTile + icon map, Card, Chip/FilterChip, Tag, Breadcrumbs, Pagination, SearchInput, Input/Select/Textarea/RadioPill with label + error, Accordion.
- A dev-only `/styleguide` page showing every component.
- **Check:** styleguide visually matches the screenshots at 390 / 834 / 1920; lint + typecheck pass.

## Phase 2 — Backend data + admin
- Migrations, models, factories and published scopes for every table in `02-architecture.md`.
- Seeder that loads `seed/mock-data.json` and attaches images from `assets/images/`.
- Filament resources and settings page; icon select from the shared icon list.
- **Check:** `php artisan migrate:fresh --seed` works; every record is editable in the admin; Pest tests for scopes and slug uniqueness.

## Phase 3 — Public API
- API Resources + controllers for every GET endpoint, internal-key middleware, Redis caching with tag-based clearing, pagination meta.
- **Check:** Pest feature tests for each endpoint (shape, published-only, pagination, 404s).

## Phase 4 — Layout + Home
- Header (desktop nav, tablet/mobile menu overlay), Footer (all data from API), `lib/api.ts`, `lib/seo.ts`.
- Home page, all 7 sections.
- **Check:** matches `Main*.png` at the three widths; Lighthouse ≥ 95 performance/accessibility on a production build.

## Phase 5 — Content pages
About, Project case study, Service landing (all 5 via one template), 404.
- **Check:** matches screenshots; every link in the page resolves (write a small link-checker script over the built site).

## Phase 6 — Blog
Shared list template for `/blog`, `/blog/page/[n]` and category routes; search page with results and empty states (`/blog/search`); single post with TOC, highlighted code, share, prev/next, related; `/page/1` → 301; out-of-range → 404.
- **Check:** matches `Blog*.png`, `BlogCategory*.png`, `BlogSearch*.png`, `SingleBlog*.png`; pagination edge cases tested.

## Phase 7 — Contact
Form, uploader (all four states), Turnstile, server action, Laravel `/uploads` + `/contact`, emails, rate limits, cleanup command, FAQ accordion.
- **Check:** Pest tests for validation, file type/size/count limits, Turnstile failure, rate limiting; manual test of all uploader states.

## Phase 8 — Revalidation, SEO, polish
Observers + `RevalidateFrontend` job + `/api/revalidate`; metadata + JSON-LD (see `05-seo-and-content.md`); sitemap/robots; security headers; OG images; 404 status; reduced motion; focus states.
- **Check:** editing a post in the admin updates `/blog`, the post and the footer within seconds without a rebuild; Rich Results test passes for Article and Breadcrumb.

## Phase 9 — Deploy
Production compose, HTTPS, backups, queue/scheduler as services, log rotation, a `DEPLOY.md` with the exact steps.
