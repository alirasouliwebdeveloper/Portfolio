# دیپلوی روی هاست cPanel (alirasouli.info)

همان روش غزل مسقط: هر `git push` روی `main` → GitHub Actions هر دو اپ را build می‌کند و در Release ثابت
`deploy-latest` منتشر می‌کند → کرون `deploy:check` روی سرور هر دقیقه چک می‌کند و نسخه‌ی جدید را خودش
دانلود، استخراج، `migrate` و کش‌ها را بازسازی می‌کند، Node.js App را ری‌استارت می‌کند و همه‌ی صفحات را از
محتوای واقعی دوباره می‌سازد. (هاست ورودی FTP/وبهوک از IPهای گیت‌هاب را می‌بندد؛ برای همین سرور خودش pull می‌کند.)

| بخش | دامنه | محل روی هاست |
|---|---|---|
| Laravel API + پنل Filament | `api.alirasouli.info` | `/home/USER/api.alirasouli.info` |
| سایت Next.js | `alirasouli.info` | Node.js App (مثلاً `/home/USER/alirasouli.info`) |

`USER` = نام‌کاربری cPanel. مسیر واقعی پوشه‌ها را در Domains چک کن.

---

## ۱. بک‌اند — یک‌بار

1. **ساب‌دامین** `api.alirasouli.info` را بساز (Domains).
2. **دیتابیس MySQL** + یوزر بساز و یوزر را با All Privileges به دیتابیس وصل کن.
3. **نسخه‌ی PHP: حتماً 8.4 یا بالاتر** (MultiPHP Manager) — پکیج‌های Symfony 8 روی 8.3 اجرا نمی‌شوند.
   اکستنشن‌های `gd`, `intl`, `zip`, `exif`, `bcmath`, `fileinfo`, `pdo_mysql` روشن باشند.
4. **اولین آپلود**: بعد از اولین push، از صفحه‌ی Releases گیت‌هاب فایل `backend.zip` را دانلود کن،
   در File Manager داخل پوشه‌ی ساب‌دامین آپلود و Extract کن. (فایل `.htaccess` ریشه همه‌ی درخواست‌ها را به
   `public/` می‌فرستد؛ لازم نیست Document Root را عوض کنی.)
5. **`.env`**: فایل `.env.cpanel.example` را به `.env` کپی کن و پر کن: اطلاعات دیتابیس، SMTP،
   `API_INTERNAL_KEY` و `REVALIDATE_SECRET` و `DEPLOY_WEBHOOK_SECRET` (هر کدام یک مقدار تصادفی بلند)،
   `TURNSTILE_SECRET_KEY`، `ADMIN_PASSWORD`، `DEPLOY_FRONTEND_PATH`، و `USER` در `SEED_PATH`/`ASSETS_PATH`.
   `DEPLOY_GITHUB_TOKEN` اختیاری است ولی توصیه می‌شود (Fine-grained PAT فقط برای همین ریپو، Contents: Read-only)
   چون بدون آن سقف ۶۰ درخواست در ساعتِ گیت‌هاب روی IP مشترک هاست زود پر می‌شود.
   این فایل هیچ‌وقت توسط دیپلوی بازنویسی نمی‌شود.
6. **راه‌اندازی اولیه** (Terminal در cPanel، یا یک Cron موقت هر دقیقه که بعد از اجرا پاکش کنی):
   ```
   cd ~/api.alirasouli.info
   php artisan key:generate --force
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan optimize
   ```
   اگر `php -v` نسخه‌ی 8.4 نبود، باینری نسخه‌دار را استفاده کن (مثلاً `/usr/local/bin/ea-php84` یا `/opt/cpanel/ea-php84/root/usr/bin/php`).
   بعد از seed، `ADMIN_PASSWORD` را از `.env` پاک کن.
7. **سه Cron دائمی** (Cron Jobs، هر دقیقه):
   ```
   * * * * * /usr/local/bin/php /home/USER/api.alirasouli.info/artisan schedule:run >> /dev/null 2>&1
   * * * * * /usr/local/bin/php /home/USER/api.alirasouli.info/artisan queue:work --stop-when-empty >> /dev/null 2>&1
   * * * * * /usr/local/bin/php /home/USER/api.alirasouli.info/artisan deploy:check >> /dev/null 2>&1
   ```
   صف (ایمیل فرم تماس، تبدیل تصاویر، revalidate سایت) بدون کرون دوم اجرا نمی‌شود.
8. ورود به پنل: `https://api.alirasouli.info/admin` — ورود دومرحله‌ای را همان‌جا فعال کن.

## ۲. فرانت‌اند — یک‌بار

1. **Setup Node.js App** → Create Application:
   Node.js **22** (حداقل 20.9)، Mode: Production، Application root: مثلاً `alirasouli.info`
   (باید دقیقاً با `DEPLOY_FRONTEND_PATH` یکی باشد)، Application URL: `alirasouli.info`،
   Startup file: **`server.js`**.
2. **Environment variables** در همان صفحه:
   ```
   API_URL=https://api.alirasouli.info/api/v1
   API_INTERNAL_KEY=<همان مقدار .env بک‌اند>
   REVALIDATE_SECRET=<همان مقدار .env بک‌اند>
   NEXT_PUBLIC_SITE_URL=https://alirasouli.info
   NEXT_PUBLIC_UPLOAD_URL=https://api.alirasouli.info/api/v1/uploads
   NEXT_PUBLIC_TURNSTILE_SITE_KEY=<site key از Cloudflare Turnstile>
   ```
3. **اولین آپلود**: `frontend.zip` را از Release دانلود، در Application root آپلود و Extract کن، بعد Restart.
   از این به بعد `deploy:check` خودش این کار را می‌کند.

## ۳. گیت‌هاب — یک‌بار

Settings → Secrets and variables → Actions:

- **Secret** `API_INTERNAL_KEY` (اختیاری): همان مقدار بک‌اند. اگر باشد و API زنده از شبکه‌ی گیت‌هاب در دسترس
  باشد، build مستقیم از محتوای واقعی ساخته می‌شود؛ وگرنه از یک API موقت با داده‌ی seed ساخته می‌شود — در هر
  دو حالت سرور بلافاصله بعد از دیپلوی همه‌ی صفحات را از محتوای واقعی بازسازی می‌کند.
- **Variable** `NEXT_PUBLIC_TURNSTILE_SITE_KEY`: site key واقعی Turnstile (در build جاسازی می‌شود).
- اختیاری: `SITE_URL` و `API_ORIGIN` اگر دامنه عوض شد، `NEXT_PUBLIC_SENTRY_DSN`.

## دیپلوی‌های بعدی

فقط `git push origin main`. ظرف چند دقیقه (build حدود ۵ دقیقه + حداکثر یک دقیقه تا تیک کرون) روی سایت است.
دیپلوی فوری بدون صبر برای کرون:
```
curl -X POST -H "X-Deploy-Token: <DEPLOY_WEBHOOK_SECRET>" https://api.alirasouli.info/deploy-hook
```
برگشت به نسخه‌ی قبل: `git revert` و push، یا اجرای دستی workflow «Deploy (cPanel)» با `ref` قدیمی‌تر.
لاگ دیپلوی‌ها: `storage/logs/laravel.log` (پیام `deploy:check ran`).

## SSL

AutoSSL در cPanel برای هر دو دامنه گواهی می‌گیرد؛ قبل از اعلام آماده بودن چک کن هر دو معتبر باشند.
