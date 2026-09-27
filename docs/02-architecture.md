# 02 — Architecture: backend, API, frontend, infra

## Overview
```
Browser ──► Next.js (static pages + server actions) ──► Laravel REST API ──► MySQL / Redis
                    ▲                                       │
                    └──── POST /api/revalidate (secret) ◄───┘  (queued job after content changes)
Admin ──► Laravel /admin (Filament)
```
- Public pages are statically generated in Next.js and cached with **fetch cache tags**. Laravel tells Next.js exactly which tags to refresh when content changes (on-demand revalidation). No time-based revalidation except a long safety net (e.g. 24h).
- The browser never talks to Laravel directly, **except** the file-upload endpoint (needed for real upload progress). Everything else goes through Next.js server components / server actions.

## Backend (Laravel)
### Packages
Filament (admin), spatie/laravel-medialibrary (images + conversions to WebP in the sizes the frontend needs), spatie/laravel-sluggable (optional), Laravel Sanctum only if a token is needed for the internal API key (a simple shared-secret middleware is enough), Pest for tests. Check each package's current version and compatibility with the installed Laravel before adding it.

### Data model
All public models: `slug` (unique), `status` (`draft|published`), `published_at`, `sort_order` where lists are ordered manually, `meta_title`, `meta_description`, media collection `og_image`. Published scope = `status = published AND published_at <= now()`.

| Table | Key fields |
|---|---|
| `settings` (single row or key/value) | name, headline, bio_short, email, phone, whatsapp, city, working_hours, socials (json), cv (media), stats (json: years, projects, clients, satisfaction), portrait (media) |
| `categories` | name, slug, description, meta fields |
| `tags` + `post_tag` | name, slug |
| `posts` | category_id, title, slug, excerpt, body (HTML from Filament RichEditor or Markdown — pick one and keep it), cover (media), featured (bool), reading_time (computed on save), author = settings, related_service_id (nullable) |
| `projects` | title, slug, summary, lead, client, role, timeline, year, live_url, challenge, solution, result, features (json: icon, title, text), stack (json), metrics (json: value, label), cover + gallery (media, with captions), testimonial_id, service_id, featured, sort_order |
| `testimonials` | quote, name, role, company, initials, avatar (media, optional), rating, featured, sort_order |
| `services` | title, slug, h1, lead, icon, hero_image (media), floating_metric (json), pains (json), offers (json), why (json: title, text, points), stack (json), tiers (json: name, price_from, subtitle, items, highlighted), faq (json), related_project_id, related_category_id, sort_order |
| `service_post` | manual related posts per service (fallback: newest posts in related category) |
| `experiences` | role, company, start_year, end_year (nullable = Present), description, sort_order |
| `process_steps` | icon, title, text, sort_order (shared by About and service pages) |
| `faqs` | question, answer, scope (`contact`), sort_order |
| `contact_messages` | name, email, company, phone, need, budget, timeline, message, service_slug, ip_hash, user_agent, status (`new|read|replied|spam`) |
| `uploads` | uuid, original_name, mime, size, path, contact_message_id (nullable), expires_at |

Icons are stored as names from a fixed set (the frontend maps names → SVG components). Offer the same set as a select in Filament.

### Public API (`/api/v1`, read-only, cached)
All GET endpoints are protected by an `X-Internal-Key` header (shared secret with Next.js) so only the frontend server reads them; responses are cached in Redis and cleared on model events.
```
GET /settings
GET /posts?page=&per_page=6&category=&exclude_featured=1
GET /search?q=&category=&sort=relevance|newest&page=   (results + per-category counts for the chips)
GET /posts/featured?category=
GET /posts/{slug}                  (includes prev, next, related[3])
GET /categories                    (with published post counts)
GET /categories/{slug}
GET /projects?featured=1
GET /projects/{slug}               (includes next project)
GET /testimonials?featured=1
GET /services                      (for footer + "other services")
GET /services/{slug}               (includes related project, testimonial, posts)
GET /experiences
GET /process-steps
GET /faqs?scope=contact
GET /sitemap                       (slugs + updated_at for every public model)
```
Pagination meta: `{ current_page, last_page, per_page, total, from, to }`.

### Contact + uploads
```
POST /uploads            (browser → Laravel, CORS limited to the frontend origin)
DELETE /uploads/{uuid}
POST /contact            (Next.js server action → Laravel, with X-Internal-Key)
```
- **Uploads:** multipart, one file per request. Allowed: pdf, doc, docx, png, jpg/jpeg, zip — validate by MIME **and** extension; max 10 MB; max 5 files per contact session (track by an upload-session id the frontend generates). Store on a private disk outside the web root, random filenames. Rate limit 20 uploads/hour/IP. Return `{ uuid, name, size }`. Errors return 422 with the exact user-facing messages from the design. A scheduled command deletes uploads not attached to a message after 24h.
- **Contact:** validate all fields (name ≤ 100, email RFC, message 10–5000 chars, need in allowed list, upload uuids exist and are unattached). Verify the **Turnstile** token server-side with Cloudflare's siteverify endpoint (secret in `.env`) and pass the visitor IP from Next.js. Rate limit 5/hour/IP. On success: save message, attach uploads, queue an email notification to Ali (with links to files in the admin, not attachments) and an auto-reply to the sender.
- Store `ip_hash` (hashed with app key), never the raw IP.

### Revalidation
Model observers (posts, categories, projects, testimonials, services, settings, experiences, process steps, faqs) dispatch a queued `RevalidateFrontend` job with the affected tags. The job POSTs `{ tags: [...] }` to `NEXT_URL/api/revalidate` with a bearer secret, retries 3× with backoff, and logs failures.
Tags: `settings`, `posts`, `post:{slug}`, `categories`, `category:{slug}`, `projects`, `project:{slug}`, `testimonials`, `services`, `service:{slug}`, `about`, `faqs`. A post change also revalidates `posts`, its category, and `services` (related posts).

### Admin (Filament)
Resources for every table above; settings as a single-record page. Post body editor with headings, code blocks, callouts and images. Contact messages resource is read-only with status actions and file downloads. Drag-and-drop ordering where `sort_order` exists. Preview links open the Next.js page.

## Frontend (Next.js)
```
src/app/
  layout.tsx, not-found.tsx, sitemap.ts, robots.ts
  page.tsx                      (home)
  about/page.tsx
  projects/[slug]/page.tsx
  blog/page.tsx
  blog/page/[n]/page.tsx
  blog/category/[slug]/page.tsx
  blog/category/[slug]/page/[n]/page.tsx
  blog/search/page.tsx         (dynamic, noindex)
  blog/[slug]/page.tsx
  contact/page.tsx
  services/[slug]/page.tsx
  api/revalidate/route.ts
src/components/{ui,layout,sections,blog,project,contact,service}
src/lib/api.ts        typed fetch wrapper: base URL, X-Internal-Key, cache tags, error handling
src/lib/seo.ts        metadata + JSON-LD builders
src/types/api.ts      response types mirroring the API Resources
```
- `generateStaticParams` for all slugs and page numbers; `dynamicParams = true` so new content works before a rebuild.
- Blog list routes share one `<PostList>` server component with props `{ page, category?, search? }`.
- Contact form: client component; uploads via `fetch`/XHR to Laravel with progress; final submit via a **server action** that forwards to Laravel with the internal key and the visitor IP. Turnstile via the official script, token sent with the submit.
- Images: `next/image` with the Laravel media domain in `remotePatterns`; always pass width/height or `fill` + `sizes`.
- Fonts: `next/font` for Inter and JetBrains Mono (self-hosted, no layout shift).
- Env: `API_URL`, `API_INTERNAL_KEY`, `REVALIDATE_SECRET`, `NEXT_PUBLIC_SITE_URL`, `NEXT_PUBLIC_UPLOAD_URL`, `NEXT_PUBLIC_TURNSTILE_SITE_KEY`.

## Infra (Docker Compose)
Services: `api` (PHP-FPM + Laravel), `nginx` (serves `api` and `/admin`), `queue` (queue worker), `scheduler`, `mysql`, `redis`, `web` (Next.js). Production: same compose on one VPS behind a reverse proxy with HTTPS; `web` on the main domain, `api` on `api.` subdomain. Nightly MySQL + media backups.

## Security checklist
CSRF not needed for the API (no cookies) but CORS locked to the frontend origin for `/uploads` only; internal key on everything else; rate limits; Turnstile; upload type/size checks; private upload disk; Filament behind strong passwords + 2FA if available; security headers (CSP, HSTS, X-Content-Type-Options) on both apps; `.env` never committed.
