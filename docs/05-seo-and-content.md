# 05 — SEO and content rules

## Metadata
Every page sets `title`, `description`, canonical URL, Open Graph and Twitter tags via `generateMetadata`. Title pattern: `{Page title} — Ali | Full-Stack Developer` (home: `Ali — Laravel & Next.js Developer`). Use the model's `meta_title`/`meta_description` when filled, otherwise title/excerpt.

| Page | Title source | Notes |
|---|---|---|
| Home | settings | JSON-LD `Person` + `WebSite` |
| About | static + settings | JSON-LD `Person`, `ProfilePage` |
| Project | project | JSON-LD `CreativeWork` + `BreadcrumbList` |
| Blog list | static | canonical = own URL (page 2 canonical is page 2, not page 1) |
| Category | category | unique description required (admin validation) |
| Post | post | JSON-LD `BlogPosting` (headline, datePublished, dateModified, author, image) + `BreadcrumbList` |
| Service | service | JSON-LD `Service` (provider = Person, areaServed from settings) + `FAQPage` + `BreadcrumbList` |
| Contact | static | JSON-LD `ContactPage` |
| Search results | — | `noindex, follow` |
| 404 | — | `noindex`, HTTP 404 |

## URLs
Lowercase kebab-case slugs, no trailing slash, no dates in post URLs. Changing a slug in the admin creates a 301 redirect from the old slug (store in a `redirects` table and handle in Next.js middleware or `redirects()` from the API).

## Sitemap & robots
`sitemap.ts` pulls `/api/v1/sitemap` and lists home, about, contact, all published posts, categories, projects and services with `lastModified`. Paginated list pages are included. `robots.ts` allows all, disallows `/api/` and `/blog/search`, points to the sitemap.

## Internal linking (already in the design — keep it)
- Footer → all service landings, latest posts, main pages.
- Home stack cards → related service landing.
- Project page → related service landing; service landing → related project and related category.
- Post body can include a "related service" card (`posts.related_service_id`); service landings list related posts.
- Every service landing links to the other four.

## Content rules
- One H1 per page. Headings in order (no skipping levels).
- All images need alt text (admin field, required for covers and gallery).
- Reading time = words / 220, rounded up, computed on save.
- Excerpts 120–160 characters (admin validation warning, not error).
