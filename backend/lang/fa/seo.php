<?php

/*
 * Persian text for the admin's live SEO analysis panel. The public site is
 * English/Arabic only — this file is read exclusively by the panel (always
 * requested with the explicit "fa" locale, see App\Support\Seo\SeoAnalyzer).
 */
return [
    'needs_keyword' => 'ابتدا کلمه کلیدی اصلی را تعیین کنید.',

    'groups' => [
        'basic' => 'سئوی پایه',
        'meta' => 'عنوان و توضیحات',
        'content' => 'محتوا',
        'media' => 'تصاویر و لینک‌ها',
    ],

    // Where to fix a failing check (the form field's Persian name).
    'fields' => [
        'title' => 'عنوان',
        'slug' => 'نامک (slug)',
        'meta_title' => 'عنوان سئو',
        'meta_description' => 'توضیحات متا',
        'focus_keyword' => 'کلمه کلیدی اصلی',
        'body' => 'محتوا',
    ],

    // Field names used inside length messages.
    'what' => [
        'meta_title' => 'عنوان سئو',
        'meta_description' => 'توضیحات متا',
    ],

    'length' => [
        'empty' => ':what خالی است — بین :range کاراکتر بنویسید.',
        'good' => ':length کاراکتر — در بازه‌ی مناسب (:range).',
        'off' => ':length کاراکتر — بین :range کاراکتر بنویسید.',
    ],

    'checks' => [
        'keyword_set' => [
            'label' => 'کلمه کلیدی اصلی',
            'good' => 'کلمه کلیدی اصلی تعیین شده است.',
            'bad' => 'یک کلمه کلیدی اصلی تعیین کنید؛ بقیه‌ی بررسی‌های کلمه کلیدی به آن وابسته‌اند.',
        ],
        'keyword_in_meta_title' => [
            'label' => 'کلمه کلیدی در عنوان سئو',
            'good' => 'کلمه کلیدی در عنوان سئو آمده است.',
            'bad' => 'کلمه کلیدی را به عنوان سئو اضافه کنید.',
        ],
        'keyword_near_start' => [
            'label' => 'کلمه کلیدی در ابتدای عنوان سئو',
            'good' => 'عنوان سئو با کلمه کلیدی شروع می‌شود.',
            'warn' => 'کلمه کلیدی را به ابتدای عنوان سئو نزدیک‌تر کنید.',
            'missing' => 'کلمه کلیدی را به عنوان سئو اضافه کنید، ترجیحاً در ابتدا.',
            'buried' => 'کلمه کلیدی ته عنوان سئو گم شده است — آن را به ابتدا ببرید.',
        ],
        'meta_title_length' => ['label' => 'طول عنوان سئو'],
        'keyword_in_meta_description' => [
            'label' => 'کلمه کلیدی در توضیحات متا',
            'good' => 'کلمه کلیدی در توضیحات متا آمده است.',
            'bad' => 'کلمه کلیدی را به توضیحات متا اضافه کنید.',
        ],
        'meta_description_length' => ['label' => 'طول توضیحات متا'],
        'keyword_in_title' => [
            'label' => 'کلمه کلیدی در عنوان صفحه (H1)',
            'good' => 'کلمه کلیدی در عنوان صفحه آمده است.',
            'bad' => 'کلمه کلیدی را به عنوان صفحه (H1) اضافه کنید.',
        ],
        'keyword_in_intro' => [
            'label' => 'کلمه کلیدی در ۱۰۰ کلمه‌ی اول',
            'good' => 'کلمه کلیدی در ابتدای محتوا آمده است.',
            'bad' => 'کلمه کلیدی را در ۱۰۰ کلمه‌ی اول محتوا بیاورید.',
        ],
        'keyword_in_h2' => [
            'label' => 'کلمه کلیدی در زیرعنوان (H2)',
            'good' => 'یکی از زیرعنوان‌های H2 شامل کلمه کلیدی است.',
            'bad' => 'کلمه کلیدی را حداقل در یک زیرعنوان H2 به کار ببرید.',
        ],
        'keyword_in_slug' => [
            'label' => 'کلمه کلیدی در نامک (slug)',
            'good' => 'نامک شامل کلمه کلیدی است.',
            'bad' => 'کلمه کلیدی را در نامک بیاورید (کلمات با خط‌تیره جدا شوند).',
            'skipped' => 'بررسی نشد: نامک بین همه‌ی زبان‌ها مشترک است و این کلمه کلیدی با حروف لاتین نیست.',
        ],
        'keyword_density' => [
            'label' => 'تراکم کلمه کلیدی',
            'no_content' => 'ابتدا محتوا بنویسید.',
            'summary' => ':count بار در :total کلمه (:percent٪)',
            'good' => ':summary — طبیعی است.',
            'low' => ':summary — کمی بیشتر از کلمه کلیدی استفاده کنید (هدف: ۰٫۵ تا ۲٫۵ درصد).',
            'high' => ':summary — کمی زیاد است (هدف: ۰٫۵ تا ۲٫۵ درصد).',
            'stuffing' => ':summary — این میزان تکرار، انباشت کلمه کلیدی محسوب می‌شود.',
            'neglected' => ':summary — کلمه کلیدی تقریباً استفاده نشده است.',
        ],
        'content_length' => [
            'label' => 'طول محتوا',
            'good' => ':count کلمه.',
            'short' => ':count کلمه — حداقل ۳۰۰ کلمه بنویسید.',
        ],
        'images' => [
            'label' => 'تصاویر و متن جایگزین (alt)',
            'none' => 'حداقل یک تصویر به محتوا اضافه کنید.',
            'missing_alt' => ':count تصویر بدون متن جایگزین (alt) دارید — تصویر را انتخاب کنید و از دکمه‌ی عکس در نوار ابزار استفاده کنید.',
            'no_keyword' => 'همه‌ی تصاویر alt دارند — کلمه کلیدی را در alt حداقل یک تصویر بیاورید.',
            'good' => 'همه‌ی تصاویر alt دارند و alt یکی از آن‌ها شامل کلمه کلیدی است.',
            'good_plain' => 'همه‌ی تصاویر alt دارند.',
        ],
        'internal_link' => [
            'label' => 'لینک داخلی',
            'good' => 'محتوا به صفحه‌ی دیگری از همین سایت لینک داده است.',
            'external_only' => 'فقط لینک خارجی دارید — یک لینک به صفحه‌ای از همین سایت اضافه کنید.',
            'none' => 'حداقل یک لینک به صفحه‌ی مرتبط در همین سایت اضافه کنید.',
        ],
        'subheading_rhythm' => [
            'label' => 'ساختار زیرعنوان‌ها',
            'none' => 'برای ساختاردهی محتوا زیرعنوان H2 اضافه کنید.',
            'good' => ':count زیرعنوان H2 برای :words کلمه.',
            'few' => 'فقط :count زیرعنوان H2 برای :words کلمه — تقریباً به ازای هر ۳۰۰ کلمه یکی لازم است.',
        ],
        'single_h1' => [
            'label' => 'فقط یک H1',
            'good' => 'عنوان صفحه تنها H1 صفحه است.',
            'no_title' => 'صفحه به عنوان نیاز دارد — همان H1 صفحه می‌شود.',
            'body_h1' => 'H1 داخل محتوا هنگام ذخیره به H2 تبدیل می‌شود.',
        ],
    ],

    'panel' => [
        'heading' => 'تحلیل سئو',
        'score' => 'امتیاز سئو',
        'language' => 'زبان در حال تحلیل',
        'languages' => ['en' => 'انگلیسی', 'ar' => 'عربی'],
        'levels' => ['good' => 'عالی', 'warn' => 'نیاز به بهبود', 'bad' => 'ضعیف'],
        'summary' => ['bad' => 'خطا', 'warn' => 'هشدار', 'good' => 'موفق'],
        'fix_in' => 'اصلاح در',
        'gsc' => [
            'heading' => 'گوگل سرچ کنسول',
            'unavailable' => 'اکنون داده‌ای در دسترس نیست (یا این صفحه هنوز در گوگل نمایشی نداشته است).',
            'period' => ':days روز اخیر برای این صفحه',
            'position' => 'میانگین رتبه',
            'impressions' => 'نمایش',
            'clicks' => 'کلیک',
            'ctr' => 'نرخ کلیک (CTR)',
            'keyword' => 'کلمه «:query»: رتبه :position، :impressions نمایش، :clicks کلیک',
            'no_keyword_data' => 'برای «:keyword» هنوز نمایشی ثبت نشده است.',
        ],
    ],

    // Messages of the "Test connection" button on the Integrations page.
    'integration' => [
        'ok' => 'اتصال موفق بود ✓ — پراپرتی «:site» در دسترس است.',
        'not_configured' => 'ابتدا فعال‌سازی، نام پراپرتی و کلید Service Account را وارد و ذخیره کنید.',
        'token_failed' => 'گوگل کلید Service Account را نپذیرفت. مطمئن شوید کل محتوای فایل JSON را بدون هیچ تغییری وارد کرده‌اید.',
        'api_disabled' => 'Search Console API روی پروژه‌ی Google Cloud فعال نیست. در Google Cloud Console بخش APIs & Services ← Library را باز کنید، «Google Search Console API» را جست‌وجو و Enable کنید.',
        'forbidden' => 'اتصال به گوگل برقرار شد، ولی این Service Account به پراپرتی «:site» دسترسی ندارد. ایمیل :email را در Search Console (Settings ← Users and permissions) به‌عنوان کاربر اضافه کنید و مطمئن شوید نام پراپرتی دقیقاً مثل Search Console نوشته شده است.',
        'unexpected' => 'پاسخ غیرمنتظره‌ای از گوگل آمد (کد :status). چند دقیقه بعد دوباره امتحان کنید.',
        'unreachable' => 'ارتباط با گوگل برقرار نشد (:error).',
    ],
];
