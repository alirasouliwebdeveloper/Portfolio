<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\ProcessStep;
use App\Support\ContentCache;
use App\Support\RichHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Small ordered lists: experiences, process steps and FAQs. */
class AboutController extends Controller
{
    use BuildsPayloads;

    public function experiences(Request $request): JsonResponse
    {
        // Not yet translatable — experience entries are the same regardless of site language.
        return response()->json(ContentCache::remember(['about'], ContentCache::key($request), fn () => [
            'data' => Experience::query()->orderByDesc('start_year')->orderBy('sort_order')->get()->map(fn (Experience $e) => [
                'role' => $e->role,
                'company' => $e->company,
                'start_year' => $e->start_year,
                'end_year' => $e->end_year,
                'description' => $e->description,
            ])->values(),
        ]));
    }

    public function processSteps(Request $request): JsonResponse
    {
        return response()->json(ContentCache::remember(['about'], ContentCache::key($request), fn () => [
            'data' => ProcessStep::query()->locale(self::locale($request))->orderBy('sort_order')->get()->map(fn (ProcessStep $s) => [
                'icon' => $s->icon,
                'title' => $s->title,
                'text' => $s->text,
            ])->values(),
        ]));
    }

    public function faqs(Request $request): JsonResponse
    {
        $params = $request->validate(['scope' => ['nullable', 'string', 'max:32']]);

        return response()->json(ContentCache::remember(['faqs'], ContentCache::key($request), fn () => [
            'data' => Faq::query()->locale(self::locale($request))->where('scope', $params['scope'] ?? 'contact')->orderBy('sort_order')->get()->map(fn (Faq $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'answer_html' => RichHtml::html($f->answer),
            ])->values(),
        ]));
    }
}
