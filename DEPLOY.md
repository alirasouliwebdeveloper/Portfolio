# Deploying to a single VPS

Everything runs from `docker-compose.prod.yml` on one server: Caddy terminates HTTPS in
front of Next.js (the public site) and Laravel (`/admin` + the API), MySQL and Redis are
private services, and a `backup` container dumps the database and uploaded media nightly.

```
                 ┌────────────┐
  Internet ─────►│   Caddy    │  :80 / :443, auto HTTPS (Let's Encrypt)
                 └─────┬──────┘
              site domain │ api domain
                 ┌────────▼────────┐   ┌──────────────┐
                 │  web (Next.js)  │   │ nginx + api   │  admin, /api/v1, uploads
                 └─────────────────┘   └──────┬────────┘
                                               │
                                    ┌──────────┼──────────┐
                                    │ queue │ scheduler │ backup
                                    └──────────┬──────────┘
                                        mysql  +  redis
```

## 0. Requirements

- A VPS with Docker Engine + the Compose plugin (`docker compose version` ≥ v2).
- Two DNS A/AAAA records pointing at the server: the main domain (e.g. `example.com`) and
  an `api.` subdomain (e.g. `api.example.com`). Caddy needs both to issue certificates.
- Ports 80 and 443 free (stop any other web server first).
- A Cloudflare Turnstile site (dashboard.cloudflare.com → Turnstile → Add site) for the
  contact form, and SMTP credentials for outgoing mail.

## 1. Get the code onto the server

```bash
git clone https://github.com/alirasouliwebdeveloper/Portfolio.git
cd Portfolio
```

## 2. Configure

```bash
cp .env.production.example .env
```

Fill in every value in `.env` (see the comments in the file):

- `SITE_DOMAIN`, `API_DOMAIN`, `ACME_EMAIL` — used by Caddy for HTTPS.
- `APP_KEY` — generate one **before** first boot:
  `docker run --rm php:8.5-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`
- `API_INTERNAL_KEY`, `REVALIDATE_SECRET`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`,
  `REDIS_PASSWORD` — random secrets, e.g. `openssl rand -hex 32`.
- `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` — from the Cloudflare dashboard.
- `MAIL_*` — your SMTP provider. `ADMIN_EMAIL` / `ADMIN_PASSWORD` — the first Filament login
  (leave `ADMIN_PASSWORD` blank to skip creating the seeded admin and make one yourself with
  `php artisan make:filament-user` instead, which is the safer option for a real launch).

Never commit `.env`; it is already git-ignored.

## 3. Build the images

The `web` image pre-renders every static page at build time, so it needs a running API.
The api/nginx images build first, run once locally on a loopback port
(`API_LOCAL_PORT`, default 8080), and the `web` build fetches content from that port.

```bash
docker compose -f docker-compose.prod.yml --env-file .env build api nginx
docker compose -f docker-compose.prod.yml --env-file .env up -d mysql redis api nginx
docker compose -f docker-compose.prod.yml --env-file .env build web
```

## 4. First boot

```bash
docker compose -f docker-compose.prod.yml --env-file .env up -d
docker compose -f docker-compose.prod.yml --env-file .env exec api php artisan db:seed --force
```

`RUN_MIGRATIONS=true` on the `api` service runs pending migrations on every start, so day-two
deploys never need a manual migrate step. The seed loads the mock content from `/seed` and
`/assets` — replace it from the admin before pointing real traffic at the site (see the
README's "Before launch" checklist).

Caddy requests certificates for `SITE_DOMAIN` and `API_DOMAIN` automatically on first
request; give it a minute, then open both domains.

## 5. Verify

- `https://SITE_DOMAIN` — the public site.
- `https://API_DOMAIN/admin` — Filament login.
- `https://SITE_DOMAIN/sitemap.xml` and `/robots.txt`.
- Edit something in the admin (e.g. a post title) and confirm the public page updates within
  seconds without a rebuild — this exercises the on-demand revalidation path end to end.
- `docker compose -f docker-compose.prod.yml --env-file .env logs -f queue` should show the
  `RevalidateFrontend` job running and finishing `DONE`.
- From the frontend repo: `SITE_URL=https://SITE_DOMAIN node frontend/scripts/check-links.mjs`
  for a broken-link / missing-metadata sweep of the live site.

## Redeploying after a code change

```bash
git pull
docker compose -f docker-compose.prod.yml --env-file .env build api nginx
docker compose -f docker-compose.prod.yml --env-file .env up -d api nginx queue scheduler
docker compose -f docker-compose.prod.yml --env-file .env build web
docker compose -f docker-compose.prod.yml --env-file .env up -d web
```
`api` migrates itself on start; `queue`/`scheduler` restart onto the new image automatically
since they share it. There is a few-second gap while `web` rebuilds — run it during low
traffic or add a second `web` replica behind Caddy if that gap ever matters.

## Backups

The `backup` service dumps MySQL (gzipped `mysqldump`) and the `storage` volume (uploaded
media, contact attachments) once a day at `BACKUP_AT` (UTC by default; set `TZ` to change
that) into `./backups` on the host, and deletes anything older than `BACKUP_RETENTION_DAYS`
(default 14). Run one on demand:

```bash
docker compose -f docker-compose.prod.yml --env-file .env exec backup /bin/bash /backup.sh now
```

Copy `./backups` off the server regularly (e.g. a nightly `rsync`/`rclone` cron job to
another machine or object storage) — a backup that only lives on the server it protects
against isn't a backup.

### Restoring

```bash
gunzip -c backups/db-<stamp>.sql.gz | docker compose -f docker-compose.prod.yml --env-file .env exec -T mysql \
  mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_DATABASE"

docker compose -f docker-compose.prod.yml --env-file .env run --rm -v "$(pwd)/backups:/backups:ro" \
  api sh -c "tar -xzf /backups/storage-<stamp>.tar.gz -C /var/www/html"
```

## Logs

Every service logs JSON to Docker's default driver, capped at 10 MB × 5 files
(`x-logging` in `docker-compose.prod.yml`) — Docker rotates them itself, so there is nothing
extra to configure. Read them with `docker compose -f docker-compose.prod.yml --env-file .env
logs -f <service>`, or point a log shipper (Vector, Fluent Bit, …) at the `json-file` driver's
files under `/var/lib/docker/containers/*/*.log` if you want them off-box.

## Notes

- `ADMIN_2FA_REQUIRED=true` by default in `.env.production.example` — Filament requires an
  authenticator app for every admin on this panel. Enable it right after the first login.
- The security headers (CSP, HSTS, `X-Frame-Options`, …) are set by `next.config.ts` for the
  site and by `docker/nginx/prod.conf` / Laravel for the API; nothing extra to configure at
  the proxy layer.
- Uploaded files and contact attachments live on the `storage` Docker volume, mounted
  read-only into `nginx` (to serve `/storage/...`) and read-write into `api`/`queue`. Back it
  up (see above) — losing it loses contact attachments and the media library.
