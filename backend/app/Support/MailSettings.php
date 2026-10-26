<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * SMTP credentials entered under Settings > Email override the .env mail config. Applied when
 * the mail manager is first resolved, so web requests and queue workers send the same way.
 */
final class MailSettings
{
    public static function apply(): void
    {
        try {
            if (! Schema::hasColumn('settings', 'mail_host')) {
                return;
            }
            $settings = Setting::query()->first();
        } catch (Throwable) {
            return; // No database yet (fresh install, package discovery).
        }

        if (! $settings || blank($settings->mail_host)) {
            return;
        }

        $encryption = $settings->mail_encryption;

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.url' => null, // a MAIL_URL in .env would win over the fields below
            'mail.mailers.smtp.host' => $settings->mail_host,
            'mail.mailers.smtp.port' => $settings->mail_port ?: ($encryption === 'ssl' ? 465 : 587),
            // Symfony: "smtps" is implicit TLS (465); "smtp" upgrades with STARTTLS when offered.
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.username' => $settings->mail_username,
            'mail.mailers.smtp.password' => $settings->mail_password,
        ]);

        if (filled($settings->mail_from_address)) {
            config(['mail.from.address' => $settings->mail_from_address]);
        }
        if (filled($settings->mail_from_name)) {
            config(['mail.from.name' => $settings->mail_from_name]);
        }
    }
}
