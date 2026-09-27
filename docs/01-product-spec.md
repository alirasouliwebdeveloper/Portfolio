# 01 — Product spec: pages, routes and behaviour

Screens for every page are in `design/screens/` as `<Page>.png` (desktop 1920), `<Page>-Tablet.png` (834) and `<Page>-Mobile.png` (390). Service landings are desktop-only in the design; build them responsive using the same patterns as the other pages.

All copy, numbers, names, prices and images in the design are **mock data** (see `seed/mock-data.json`). Seed them so the site looks finished, but everything must be editable in Filament.

## Global layout
- **Header:** logo (`</>` mark + wordmark), nav (Home, About, Projects, Blog, Contact) with active underline, "Hire Me" button → `/contact`. Tablet: logo + Hire Me + menu button. Mobile: logo + menu button. The open menu state is not designed: build a full-screen overlay using the same tokens (large nav links, Hire Me button, socials), focus-trapped, closes on Esc and route change.
- **Footer:** 5 columns on desktop — brand + short bio + socials; Pages; Services (links to published service landings, from API); Latest articles (4 newest posts, from API); Get in touch (email, phone, city, working hours). Bottom bar: © year, Privacy Policy, Terms of Service, Sitemap. Tablet: brand on top, 2-column grid below. Mobile: stacked.
- **Container:** max-width 1440px, centered. Horizontal padding: 40px (tablet), 20px (mobile). Section vertical padding 96 / 72 / 56 (desktop / tablet / mobile).
- Alternating section backgrounds: `bg` and `bg-alt` (see tokens).

## Routes
| Route | Page | Screen |
|---|---|---|
| `/` | Home | Main |
| `/about` | About | About |
| `/projects/[slug]` | Project case study | SingleProject |
| `/blog` | Blog list, page 1 | Blog |
| `/blog/page/[n]` | Blog list, page n ≥ 2 | BlogPage2 |
| `/blog/category/[slug]` | Category list, page 1 | BlogCategory |
| `/blog/category/[slug]/page/[n]` | Category list, page n | (BlogPage2 pattern) |
| `/blog/search?q=` | Search results / no results | BlogSearch, BlogSearchEmpty |
| `/blog/[slug]` | Single post | SingleBlog |
| `/contact` | Contact | Contact |
| `/services/[slug]` | Service landing | Service* |
| any unknown | 404 (`app/not-found.tsx`) | NotFound |

Also: `/sitemap.xml`, `/robots.txt`, `/privacy`, `/terms` (simple text pages — not designed; reuse the SingleBlog body typography). A projects index is **not** designed: "Projects" in the nav and "All Projects" links go to the Featured Projects section on Home (`/#projects`) until an index page is designed.

## Home (`/`)
1. **Hero** — chip "I'M A WEB DEVELOPER", H1 "Hi, I'm Ali" (name in accent-soft), sub-headline, lead, buttons (View My Work → featured project, Download CV → CV file from settings), "Technologies I work with" icon row. Right: portrait inside an arch shape (rounded top), dot grid, curved arrow, floating code card. Tablet/mobile: text first, portrait below with the code card overlapping bottom-left.
2. **About teaser** — chip, heading, paragraph, outline button → `/about`; 2×2 stat grid with icon tiles (from settings).
3. **Stack ("What I Build With")** — 4 cards (Backend, Frontend, DevOps, Automation): icon tile + title, description, tech tags, footer link to the related service landing.
4. **Featured Projects** (`id="projects"`) — 3 project cards (image, title, summary, 2 tags, "View Project").
5. **Testimonials** — 3 cards (quote icon, 5 stars, quote, avatar initials, name, role).
6. **Latest Articles** — 3 post cards + "View All Articles".
7. **Contact teaser** — heading + contact rows (email, phone, city) and a short form (name, email, need, details). Submits to the same contact endpoint (no uploads here).

## About (`/about`)
Hero (chip, H1, two paragraphs, Get In Touch + Download CV, arch portrait) → How I work (4 step cards with icon tile and "Step n") → Experience timeline (from `experiences`; newest first; first dot uses accent) → Toolbox (4 groups with icon tile + tags) → CTA band.

## Project case study (`/projects/[slug]`)
Breadcrumb → eyebrow "CASE STUDY", H1, lead, "Visit Live Site" (only if URL set) + outline link to the related service landing → 4 meta cards (Client, My role, Timeline, Year) → main screenshot → Overview (Challenge / Solution / Result cards with icon tiles) → Key features (6 cards, icon tile vertically centered with its text) → Screens gallery (2×2, captions) → Built with (tags) + 3 result metrics + client quote → Next project card.

## Blog list (`/blog`, `/blog/page/[n]`, `/blog/category/[slug]...`)
One template for all four routes.
- **Header:** breadcrumb, eyebrow (BLOG or CATEGORY), H1 (Articles & Notes, or category name), description (blog intro or category description), search input, category chips with post counts (active chip = current category, "All" on /blog).
- **Featured post:** only on page 1 (blog: newest featured post; category: newest featured post in that category). It is excluded from the grid on that page.
- **Grid:** 6 post cards per page (3/2/1 columns) with "Showing x–y of total".
- **Pagination:** Prev / numbers with ellipsis / Next; mobile shows "Page n of N". Page 1 lives at the list root — `/page/1` must 301 to the root. Pages ≥ 2 use a compact header: no description, no featured post, H1 suffix "— Page n". Out-of-range pages → 404.
- **Search:** `/blog/search?q=term` — designed as `BlogSearch*` (results) and `BlogSearchEmpty*` (no results). See the section below.

## Blog search (`/blog/search?q=`)
- **Header:** breadcrumb (Home / Blog / Search), eyebrow SEARCH, H1 "Results for “term”" (term in `icon-soft`), result count, search form prefilled with the term (accent border, clear button → `/blog`, Search button; full-width button on mobile). The search inputs on the blog list and 404 pages submit here (GET form, `q` param).
- **Results:** category filter chips built from the result set with counts ("All 9", "Laravel 6"…; filter via `&category=`), sort select (Relevance default, Newest), "Showing x–y of total", the same post-card grid as the blog, matched words highlighted with `<mark>` in title and excerpt, pagination (`&page=`).
- **No results:** H1 "No results for “term”", helper text, a card with search icon tile, tips list, "View All Articles", popular search chips (links to `/blog/search?q=…`, list editable in settings), "Browse by topic" category cards with counts; then a Popular articles section.
- Behaviour: trim the query, min 2 characters (shorter → redirect to `/blog`), max 100. Search title, excerpt and body (Laravel Scout with the database driver is enough to start; relevance = title matches first). Rendered dynamically (not static), `noindex, follow`, not in the sitemap. Empty `q` → redirect to `/blog`.

## Single post (`/blog/[slug]`)
Breadcrumb → category chip, H1, excerpt, author (photo, name, date · read time) → wide cover → desktop 3 columns: sticky "On this page" TOC (from H2s, active item highlighted on scroll), article (max 800px), share (LinkedIn, X, copy link). Tablet/mobile: TOC in a collapsible box above the article, share row below. Body supports H2/H3, paragraphs, lists, inline code, code blocks with server-side syntax highlighting (e.g. Shiki), callout, figure + caption, and an inline "related service" link card. Then tags, author box, prev/next posts, related posts (same category first).

## Contact (`/contact`)
Header → form + aside (desktop: form flexible, aside 460px).
- **Fields:** name*, email*, company, phone/WhatsApp, "What do you need?" radio pills (Website or web app, Online store, API or backend, Automation, Something else; preselect from `?service=`), budget select, timeline select, project details*.
- **Uploader:** dropzone ("Choose files or drag them here"), limits "PDF, DOC, DOCX, PNG, JPG or ZIP · up to 10 MB each · max 5 files", counter "n of 5 files". File rows show the designed states: uploaded (size + green check), uploading (progress bar + %), error "This file type isn't supported.", error "24 MB — files must be 10 MB or smaller." Each row has a remove button. Validate on the client before upload and again on the server.
- **Cloudflare Turnstile** ("Security check") above the submit row; submit disabled until it passes.
- **Success/failure:** not designed — on success replace the form with a confirmation card (icon tile + "Thanks, {name} — I'll reply within one working day."). On failure show an error summary at the top of the form and field messages under inputs (danger color + alert icon, same style as the upload errors).
- **Aside:** email, phone/WhatsApp, city, response time cards + socials.
- **FAQ:** 8 questions (order editable in admin), accordion, first open by default, one open at a time, animated height (respect reduced motion), `aria-expanded` / `aria-controls`.

## Service landing (`/services/[slug]`)
Hero (breadcrumb, icon + SERVICE eyebrow, keyword H1, lead, Get a Free Quote → `/contact?service=<slug>`, See Related Work → `#work`, trust row, image with floating metric card) → Is this for you? (3 pain cards) → What's included (6 offer cards) → The stack (why text + checklist + tools card linking to About) → How it works (4 steps) → Related work (`#work`: case study card with 3 metrics → project page, client quote) → Pricing (3 tiers, middle "Most popular", CTA → contact) → FAQ (service-specific, first open) → Related articles (3 posts + link to related category) → Other services (other 4 landings) → CTA band.
Slugs: `laravel-development`, `nextjs-website-development`, `online-store-development`, `api-development`, `n8n-automation`.

## 404
Gradient "404", H1, text, site search, Back to Home / Browse the Blog, Quick links (4) and Popular articles (3). Must return HTTP 404.
