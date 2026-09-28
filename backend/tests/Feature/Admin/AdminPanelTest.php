<?php

use App\Enums\ContactMessageStatus;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\ProcessSteps\ProcessStepResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

function withCover(Post|Project|Setting $model, string $collection = 'cover'): void
{
    $model->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 750))->toMediaCollection($collection);
}

it('redirects guests to the login page', function () {
    auth()->logout();

    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get(PostResource::getUrl('index'))->assertRedirect('/admin/login');
});

it('renders the list and edit pages of every resource', function (string $resource, Closure $record) {
    $model = $record();

    $this->get($resource::getUrl('index'))->assertOk();
    $this->get($resource::getUrl('edit', ['record' => $model]))->assertOk();
})->with([
    'posts' => [PostResource::class, fn () => Post::factory()->create()],
    'categories' => [CategoryResource::class, fn () => Category::factory()->create()],
    'tags' => [TagResource::class, fn () => Tag::factory()->create()],
    'projects' => [ProjectResource::class, fn () => Project::factory()->create()],
    'services' => [ServiceResource::class, fn () => Service::factory()->create()],
    'testimonials' => [TestimonialResource::class, fn () => Testimonial::factory()->create()],
    'experiences' => [ExperienceResource::class, fn () => Experience::factory()->create()],
    'process steps' => [ProcessStepResource::class, fn () => ProcessStep::factory()->create()],
    'faqs' => [FaqResource::class, fn () => Faq::factory()->create()],
    'pages' => [PageResource::class, fn () => Page::factory()->create(['key' => 'home'])],
]);

it('renders the create page of every editable resource', function (string $resource) {
    $this->get($resource::getUrl('create'))->assertOk();
})->with([
    PostResource::class,
    CategoryResource::class,
    TagResource::class,
    ProjectResource::class,
    ServiceResource::class,
    TestimonialResource::class,
    ExperienceResource::class,
    ProcessStepResource::class,
    FaqResource::class,
]);

it('requires the essential fields when creating a post', function () {
    Livewire::test(CreatePost::class)
        ->call('create')
        ->assertHasFormErrors(['title' => 'required', 'excerpt' => 'required', 'category_id' => 'required', 'cover_alt' => 'required']);
});

it('rejects slugs that are not kebab-case', function () {
    $post = Post::factory()->create();
    withCover($post);

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['slug' => 'Not A Slug'])
        ->call('save')
        ->assertHasFormErrors(['slug']);
});

it('rejects a slug that another post already uses', function () {
    $other = Post::factory()->create(['slug' => 'taken']);
    $post = Post::factory()->create();
    withCover($post);

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['slug' => $other->slug])
        ->call('save')
        ->assertHasFormErrors(['slug']);
});

it('saves an edited post and keeps its slug', function () {
    $post = Post::factory()->create(['title' => 'Old title']);
    withCover($post);
    $slug = $post->slug;

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['title' => 'A brand new title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh())->title->toBe('A brand new title')->slug->toBe($slug);
});

it('shows a single settings page and saves changes', function () {
    withCover(Setting::factory()->create(), 'portrait');

    $this->get(SettingResource::getUrl('index'))->assertOk();

    Livewire::test(EditSetting::class)
        ->fillForm(['headline' => 'Shipping fast websites', 'email' => 'new@example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::count())->toBe(1)
        ->and(Setting::first())->headline->toBe('Shipping fast websites');
});

it('creates the settings record on first visit', function () {
    expect(Setting::count())->toBe(0);

    $this->get(SettingResource::getUrl('index'))->assertOk();

    expect(Setting::count())->toBe(1);
});

it('lists contact messages read-only and lets an admin change their status', function () {
    $message = ContactMessage::factory()->create();

    $this->get(ContactMessageResource::getUrl('index'))->assertOk();
    expect(ContactMessageResource::canCreate())->toBeFalse();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getKey()])
        ->assertOk()
        ->callAction('mark_replied');

    expect($message->fresh()->status)->toBe(ContactMessageStatus::Replied);
});

it('edits a fixed page and its SEO fields, and cannot create or delete pages', function () {
    $page = Page::factory()->create(['key' => 'about', 'title' => 'About', 'content' => ['hero' => ['chip' => 'ABOUT ME']]]);

    Livewire::test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm(['title' => 'About Ali', 'focus_keyword' => 'full-stack developer', 'meta_title' => 'About Ali | Full-stack developer'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->fresh())->title->toBe('About Ali')->focus_keyword->toBe('full-stack developer')
        ->and($page->fresh()->seo_score)->not->toBeNull()
        ->and(PageResource::canCreate())->toBeFalse()
        ->and(PageResource::canDelete($page))->toBeFalse();
});
