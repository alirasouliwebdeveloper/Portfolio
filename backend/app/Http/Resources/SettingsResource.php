<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Setting */
class SettingsResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        // Only a handful of Settings fields read as prose; the rest (name, contact details,
        // socials, ...) are the same regardless of site language. Falls back to English when a
        // Persian value hasn't been filled in yet, so the site never shows an empty string.
        $locale = self::locale($request);
        $localized = fn (?string $fa, ?string $en) => $locale === 'fa' && filled($fa) ? $fa : $en;

        return [
            'brand' => [
                'name' => $this->brand_name,
                'tagline' => $localized($this->tagline_fa, $this->tagline),
                'logo' => self::imageOf($this->resource, 'logo', $this->brand_name),
                'favicon' => $this->getFirstMediaUrl('favicon') ?: null,
                'footer_text' => $localized($this->footer_text_fa, $this->footer_text),
            ],
            'profile' => [
                'name' => $this->name,
                'headline' => $localized($this->headline_fa, $this->headline),
                'bio_short' => $localized($this->bio_short_fa, $this->bio_short),
                'portrait' => self::imageOf($this->resource, 'portrait', $this->portrait_alt),
                'cv_url' => $this->getFirstMediaUrl('cv') ?: null,
            ],
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'city' => $this->city,
                'working_hours' => $this->working_hours,
                'response_time' => $this->response_time,
            ],
            'socials' => array_filter((array) $this->socials),
            'stats' => ($locale === 'fa' && filled($this->stats_fa) ? $this->stats_fa : $this->stats) ?? [],
            'popular_searches' => $this->popular_searches ?? [],
            'contact_options' => [
                'needs' => $this->contact_options['needs'] ?? [],
                'budgets' => $this->contact_options['budgets'] ?? [],
                'timelines' => $this->contact_options['timelines'] ?? [],
                'upload' => [
                    'types' => config('portfolio.upload.types'),
                    'max_mb' => config('portfolio.upload.max_mb'),
                    'max_files' => config('portfolio.upload.max_files'),
                ],
            ],
            'tracking' => [
                'ga_measurement_id' => $this->ga_measurement_id,
                'gsc_verification' => $this->gsc_verification,
                'twitter_handle' => $this->twitter_handle,
            ],
            'site_noindex' => (bool) $this->site_noindex,
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description ?: $this->bio_short,
                'og_image' => self::imageOf($this->resource, 'og_image'),
            ],
        ];
    }
}
