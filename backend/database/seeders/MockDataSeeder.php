<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Support\TipTapDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;
use Tiptap\Editor;

/**
 * Loads seed/mock-data.json (mock content from the design) and attaches
 * images from assets/images. Everything stays editable in Filament.
 */
class MockDataSeeder extends Seeder
{
    private const TAG_KEYWORDS = ['Laravel', 'Filament', 'Docker', 'n8n', 'Next.js', 'React', 'SEO', 'Redis', 'MySQL', 'Tailwind'];

    /** Post category name => service slug used for the "related service" card. */
    private const CATEGORY_SERVICE = [
        'Laravel' => 'laravel-development',
        'Next.js' => 'nextjs-website-development',
        'Automation' => 'n8n-automation',
    ];

    private array $data;

    public function run(): void
    {
        if (Category::query()->exists()) {
            $this->command?->warn('Content already seeded; skipping mock data.');

            return;
        }

        $file = rtrim(config('portfolio.seed_path'), '/').'/mock-data.json';
        if (! is_file($file)) {
            throw new RuntimeException("Seed file not found: {$file}");
        }
        $this->data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

        config(['media-library.queue_conversions_by_default' => false]);

        $this->settings();
        $categories = $this->categories();
        $testimonials = $this->testimonials();
        $services = $this->services($categories);
        $projects = $this->projects($testimonials, $services);
        $this->linkServiceProjects($services, $projects);
        $this->posts($categories, $services);
        $this->experiences();
        $this->processSteps();
        $this->faqs();

        Artisan::call('seo:rescore');
    }

    private function settings(): void
    {
        $s = $this->data['settings'];
        $options = $this->data['contact_options'];

        $setting = Setting::create([
            'name' => $s['name'],
            'headline' => $s['headline'],
            'bio_short' => $s['bio_short'],
            'email' => $s['email'],
            'phone' => $s['phone'],
            'whatsapp' => $s['whatsapp'],
            'city' => $s['city'],
            'working_hours' => $s['working_hours'],
            'response_time' => $s['response_time'],
            'socials' => $this->socials($s['socials']),
            'stats' => $s['stats'],
            'popular_searches' => ['Laravel', 'Filament', 'Docker', 'n8n', 'SEO'],
            'contact_options' => [
                'needs' => $options['needs'],
                'budgets' => $options['budgets'],
                'timelines' => $options['timelines'],
            ],
            'portrait_alt' => 'Portrait of '.$s['name'],
            'meta_title' => 'Ali — Laravel & Next.js Developer',
            'meta_description' => $s['bio_short'],
        ]);

        $this->attach($setting, $s['portrait'], 'portrait');
    }

    /** The design uses "#" placeholders; the admin validates URLs, so seed valid generic ones. */
    private function socials(array $socials): array
    {
        $placeholders = [
            'github' => 'https://github.com/',
            'linkedin' => 'https://www.linkedin.com/',
            'x' => 'https://x.com/',
            'instagram' => 'https://www.instagram.com/',
        ];

        return collect($socials)
            ->map(fn ($url, $network) => $url === '#' ? ($placeholders[$network] ?? null) : $url)
            ->all();
    }

    /** @return array<string, Category> keyed by name */
    private function categories(): array
    {
        $result = [];
        foreach ($this->data['categories'] as $index => $row) {
            $result[$row['name']] = Category::create([
                ...$row,
                'sort_order' => $index,
                'status' => PublishStatus::Published,
                'published_at' => now()->subYear(),
            ]);
        }

        return $result;
    }

    /** @return array<string, Testimonial> keyed by name */
    private function testimonials(): array
    {
        $result = [];
        foreach ($this->data['testimonials'] as $index => $row) {
            $result[$row['name']] = Testimonial::create([...$row, 'sort_order' => $index]);
        }

        return $result;
    }

    /** @return array<string, Service> keyed by slug */
    private function services(array $categories): array
    {
        $bySlug = collect($categories)->keyBy('slug');
        $result = [];

        foreach ($this->data['services'] as $index => $row) {
            $service = Service::create([
                'slug' => $row['slug'],
                'nav_label' => $row['nav_label'],
                'title' => $row['title'],
                'h1' => $row['h1'],
                'lead' => $row['lead'],
                'icon' => $row['icon'],
                'hero_image_alt' => $row['h1'],
                'floating_metric' => $row['floating_metric'],
                'pains' => $row['pains'],
                'offers' => $row['offers'],
                'why' => [...$row['why'], 'text' => TipTapDocument::fromParagraphs($row['why']['text'] ?? null)],
                'stack' => $row['stack'],
                'tiers' => $row['tiers'],
                'faq' => $row['faq'],
                'related_category_id' => $bySlug[$row['related_category']]->id ?? null,
                'sort_order' => $index,
                'status' => PublishStatus::Published,
                'published_at' => now()->subYear(),
            ]);
            $this->attach($service, $row['hero_image'], 'hero_image');
            $result[$row['slug']] = $service;
        }

        return $result;
    }

    /** @return array<string, Project> keyed by slug */
    private function projects(array $testimonials, array $services): array
    {
        $result = [];

        foreach ($this->data['projects'] as $index => $row) {
            $project = Project::create([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'summary' => $row['summary'],
                'lead' => TipTapDocument::fromParagraphs($row['lead'] ?? null),
                'client' => $row['client'] ?? null,
                'role' => $row['role'] ?? null,
                'timeline' => $row['timeline'] ?? null,
                'year' => $row['year'] ?? null,
                'live_url' => $row['live_url'] ?? null,
                'challenge' => TipTapDocument::fromParagraphs($row['challenge'] ?? null),
                'solution' => TipTapDocument::fromParagraphs($row['solution'] ?? null),
                'result' => TipTapDocument::fromParagraphs($row['result'] ?? null),
                'features' => $row['features'] ?? null,
                'stack' => $row['stack'] ?? null,
                'metrics' => $row['metrics'] ?? null,
                'cover_alt' => $row['title'].' — main screenshot',
                'testimonial_id' => ($testimonials[$row['testimonial'] ?? ''] ?? null)?->id,
                'service_id' => ($services[$row['service'] ?? ''] ?? null)?->id,
                'featured' => $row['featured'] ?? false,
                'sort_order' => $index,
                'status' => PublishStatus::Published,
                'published_at' => now()->subYear(),
            ]);
            $this->attach($project, $row['cover'], 'cover');

            foreach ($row['gallery'] ?? [] as $order => $shot) {
                $screen = $project->screens()->create([
                    'caption' => $shot['caption'],
                    'alt' => $row['title'].' — '.$shot['caption'],
                    'sort_order' => $order,
                ]);
                $this->attach($screen, $shot['file'], 'image');
            }
            $result[$row['slug']] = $project;
        }

        return $result;
    }

    private function linkServiceProjects(array $services, array $projects): void
    {
        foreach ($this->data['services'] as $row) {
            $project = $projects[$row['related_project'] ?? ''] ?? null;
            if ($project) {
                $services[$row['slug']]->update(['related_project_id' => $project->id]);
            }
        }
    }

    private function posts(array $categories, array $services): void
    {
        $tags = [];
        $created = [];

        // Events are off so the designed reading time is kept instead of recomputed from the placeholder body.
        Post::withoutEvents(function () use ($categories, $services, &$tags, &$created): void {
            foreach ($this->data['posts'] as $row) {
                $post = Post::create([
                    'category_id' => $categories[$row['category']]->id,
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'excerpt' => $row['excerpt'],
                    'body' => (new Editor)->setContent($this->body($row))->getDocument(),
                    'featured' => $row['featured'],
                    'reading_time' => $row['reading_time'],
                    'cover_alt' => $row['title'],
                    'related_service_id' => ($services[self::CATEGORY_SERVICE[$row['category']] ?? ''] ?? null)?->id,
                    'status' => PublishStatus::Published,
                    'published_at' => Carbon::parse($row['published_at'])->setTime(9, 0),
                ]);

                $names = collect([$row['category']])
                    ->merge(collect(self::TAG_KEYWORDS)->filter(fn ($k) => Str::contains($row['title'].' '.$row['excerpt'], $k)))
                    ->unique()
                    ->values();
                $post->tags()->sync($names->map(fn ($name) => ($tags[$name] ??= Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]))->id));

                $created[] = [$post, $row['cover']];
            }
        });

        // Media must be attached with events on, otherwise the media library never assigns the uuid.
        foreach ($created as [$post, $cover]) {
            $this->attach($post, $cover, 'cover');
        }
    }

    private function experiences(): void
    {
        foreach ($this->data['experiences'] as $index => $row) {
            Experience::create([...$row, 'sort_order' => $index]);
        }
    }

    private function processSteps(): void
    {
        foreach ($this->data['process_steps'] as $index => $row) {
            ProcessStep::create([...$row, 'sort_order' => $index]);
        }
    }

    private function faqs(): void
    {
        foreach ($this->data['faqs'] as $index => $row) {
            Faq::create([...$row, 'answer' => TipTapDocument::fromParagraphs($row['answer']), 'sort_order' => $index]);
        }
    }

    /** Placeholder article body (mock): headings for the TOC, a list and a code block. */
    private function body(array $post): string
    {
        $excerpt = e($post['excerpt']);

        return <<<HTML
<p>{$excerpt} This is placeholder text for the design — replace it with the real article in the admin panel.</p>
<h2>The problem</h2>
<p>Most projects start with a simple requirement and quietly grow into something harder to change. Naming the constraints early makes every later decision cheaper.</p>
<h2>The approach</h2>
<p>Keep the moving parts few and make each one easy to replace:</p>
<ul>
<li>Model the data first, then expose it through a small, consistent API.</li>
<li>Cache what is read often and clear it when the content changes.</li>
<li>Automate the boring parts so releases stay predictable.</li>
</ul>
<pre><code class="language-php">public function toArray(Request \$request): array
{
    return [
        'title' => \$this->title,
        'slug' => \$this->slug,
    ];
}</code></pre>
<h2>What I would do differently</h2>
<p>Start with the smallest version that works, measure it, and only then add the next layer.</p>
HTML;
    }

    private function attach(HasMedia&Model $model, string $file, string $collection): void
    {
        $path = rtrim(config('portfolio.assets_path'), '/').'/'.$file;
        if (! is_file($path)) {
            throw new RuntimeException("Seed image not found: {$path}");
        }

        $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
    }
}
