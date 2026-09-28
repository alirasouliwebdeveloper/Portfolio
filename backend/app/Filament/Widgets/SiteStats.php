<?php

namespace App\Filament\Widgets;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $scores = collect([Post::query(), Project::query()])
            ->flatMap(fn ($query) => $query->whereNotNull('seo_score')->pluck('seo_score'));
        $average = $scores->isEmpty() ? null : (int) round($scores->avg());
        $newMessages = ContactMessage::query()->where('status', ContactMessageStatus::New)->count();

        return [
            Stat::make('Published articles', Post::published()->count())
                ->description(Post::query()->where('status', 'draft')->count().' drafts')
                ->icon('heroicon-o-document-text'),
            Stat::make('Projects', Project::published()->count())->icon('heroicon-o-briefcase'),
            Stat::make('New messages', $newMessages)
                ->color($newMessages > 0 ? 'primary' : 'gray')
                ->icon('heroicon-o-inbox'),
            Stat::make('Average SEO score', $average === null ? '—' : $average.' / 100')
                ->color(match (true) {
                    $average === null => 'gray',
                    $average >= 80 => 'success',
                    $average >= 50 => 'warning',
                    default => 'danger',
                })
                ->description('Articles and projects')
                ->icon('heroicon-o-magnifying-glass'),
        ];
    }
}
