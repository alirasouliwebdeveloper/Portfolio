# Ali Portfolio

Developer portfolio and blog: Laravel REST API + Filament admin panel, and a Next.js
(App Router) frontend that renders static pages refreshed by on-demand revalidation.

- **Backend:** `backend/` — Laravel 13, Filament 5 admin at `/admin`, MySQL, Redis (cache +
  queues). See `docs/02-architecture.md` for the data model and API.
- **Frontend:** `frontend/` — Next.js, TypeScript (strict), Tailwind CSS. Content comes from
  the API; pages are statically generated and revalidated on demand when content changes.
- **Design source:** `docs/` (spec, architecture, design system, SEO), `design/` (screens +
  reference HTML at 1920/834/390), `seed/mock-data.json` (seeder content, replace before
  launch), `assets/` (placeholder images, replace before launch).

## Local development

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
docker compose up -d
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```

- Site: http://localhost:3000
- API: http://localhost:8000/api/v1 (or `${NGINX_PORT}`, see `.env`/`.env.example` at the repo root)
- Admin: http://localhost:8000/admin (credentials from `ADMIN_EMAIL`/`ADMIN_PASSWORD` in `backend/.env`)

## Commands

Backend (inside the `api` container, e.g. `docker compose exec api ...`):
```bash
php artisan test      # Pest
./vendor/bin/pint     # code style
```

Frontend:
```bash
npm run dev            # http://localhost:3000
npm run build
npm run lint
npm run typecheck
npm test                # Vitest
npm run check:links     # crawl a running site for broken links / missing metadata
```

## Deploying

See `DEPLOY.md` for the production Docker Compose stack (Caddy + HTTPS, backups, log
rotation) on a single VPS.

## Before launch — replace mock content

Stats, testimonials, project details/results, experience, service prices, FAQ answers,
contact details, social links, project screenshots, blog covers and the CV file are seeded
from `seed/mock-data.json` and `assets/` — edit them from the admin panel (or the seed data
before the first seed) before pointing real traffic at the site. Two projects
(Task Management, Crypto Dashboard) only have card data and need case-study content written
in the admin. Set each project's "Visit Live Site" URL once it has one.
