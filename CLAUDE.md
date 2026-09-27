# CLAUDE.md — Ali Portfolio (Laravel API + Next.js)

You are building Ali's personal portfolio website from a finished design. Read this file first, then the files in `docs/` in numeric order before writing any code.

## What this project is
An English-language developer portfolio: home, about, projects (case studies), blog (list, pagination, categories, single post), contact (form with file uploads + Cloudflare Turnstile), 5 SEO service landing pages and a 404 page. Content is managed in an admin panel. The same UI will later be reused for a Persian (RTL) site, so the frontend must be RTL-ready from day one.

## Stack (decided — do not change without asking)
- **Backend:** Laravel (latest stable) as a **REST API only** + **Filament** (latest stable compatible) admin panel at `/admin`. MySQL, Redis (cache + queues).
- **Frontend:** Next.js (latest stable, **App Router**), TypeScript (strict), Tailwind CSS. Pages are statically generated and refreshed with **on-demand revalidation** triggered by Laravel.
- **Infra:** Docker Compose for local dev and a single-VPS production setup.
- Verify current stable versions and their official docs before installing. Do not rely on memory for version-specific APIs; never invent framework features.

## Repository layout
```
/backend     Laravel app (API + Filament)
/frontend    Next.js app
/docker      Dockerfiles, nginx config
/docs        Specs (this handoff)
/design      Screenshots + static HTML reference of every artboard
/assets      Images used in the design (mock — replace before launch)
/seed        mock-data.json used by the database seeder
docker-compose.yml
```

## Golden rules
1. **Design fidelity:** match `design/screens/*.png` at 1920 / 834 / 390 widths. Use `design/html/*.html` to read exact spacing, sizes and colors. Do **not** copy inline styles — build reusable components with the tokens in `docs/03-design-system.md`.
2. **Tokens only:** no hard-coded hex colors, font sizes or radii in components. Everything comes from the Tailwind theme / CSS variables.
3. **RTL-ready:** use logical utilities only (`ms-/me-/ps-/pe-/start-/end-/text-start`). Never `ml-/mr-/pl-/pr-/left-/right-` for layout. Directional icons (arrows, chevrons) flip with `rtl:` variants. Code blocks stay `dir="ltr"`.
4. **Accessibility:** real `<button>`/`<a>`/`<label>`, visible focus rings, `aria-*` where the design shows icon-only controls, `prefers-reduced-motion` respected, text contrast ≥ 4.5:1.
5. **Content comes from the API.** No hard-coded copy in the frontend except UI labels. Footer service links, contact details and socials come from settings/services endpoints.
6. **Security:** validate every input server-side, rate-limit public POST endpoints, verify Turnstile server-side, never expose the admin or internal API keys to the browser.
7. **Work in phases** (`docs/04-implementation-plan.md`). Finish a phase, run its checks, summarize what changed, then continue. Ask before making architectural changes.

## Commands (create these as you scaffold)
- `docker compose up -d` — full stack locally
- Backend: `php artisan test` (Pest), `./vendor/bin/pint`
- Frontend: `npm run dev`, `npm run build`, `npm run lint`, `npm run typecheck`

## Conventions
- API: `/api/v1/...`, JSON via Laravel API Resources, consistent envelope for collections: `{ data: [...], meta: { current_page, last_page, per_page, total } }`.
- Slugs are unique, kebab-case, and are the public identifiers in URLs and API.
- DB: snake_case tables/columns, `status` enum `draft|published`, `published_at` nullable timestamp, SEO fields on every public model: `meta_title`, `meta_description`, `og_image` (media).
- Frontend: components in `src/components/{ui,layout,sections,blog,project,contact,service}`; data access only through `src/lib/api.ts` (typed fetch wrapper that sets Next cache tags).
- Commits: small, conventional (`feat:`, `fix:`, `chore:`).
