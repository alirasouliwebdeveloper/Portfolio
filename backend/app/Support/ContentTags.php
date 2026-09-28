<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\ProjectScreen;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Cache/revalidation tags affected when a model changes. The same tags are used for the Redis
 * API cache and for Next.js on-demand revalidation (`docs/02-architecture.md`).
 */
final class ContentTags
{
    /** @return list<string> */
    public static function for(Model $model): array
    {
        if ($model instanceof Media) {
            $owner = $model->model;

            return $owner instanceof Model ? self::for($owner) : [];
        }

        return array_values(array_unique(match (true) {
            $model instanceof Post => [
                'posts', "post:{$model->slug}", 'categories', 'services', 'search', 'sitemap',
                ...($model->category ? ["category:{$model->category->slug}"] : []),
            ],
            $model instanceof Category => ['categories', "category:{$model->slug}", 'posts', 'services', 'search', 'sitemap'],
            $model instanceof Tag => ['posts'],
            $model instanceof Project => ['projects', "project:{$model->slug}", 'services', 'sitemap'],
            $model instanceof ProjectScreen => $model->project ? ['projects', "project:{$model->project->slug}"] : ['projects'],
            $model instanceof Testimonial => ['testimonials', 'projects', 'services'],
            $model instanceof Service => ['services', "service:{$model->slug}", 'settings', 'sitemap'],
            $model instanceof Setting => ['settings', 'posts', 'projects', 'services', 'pages'],
            $model instanceof Page => ['pages', 'sitemap', ...($model->key === 'about' ? ['about'] : [])],
            $model instanceof Experience, $model instanceof ProcessStep => ['about', 'services'],
            $model instanceof Faq => ['faqs'],
            default => [],
        }));
    }
}
