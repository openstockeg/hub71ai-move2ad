<?php

namespace App\Http\Controllers;

use App\Models\Brief;
use App\Services\BriefGenerator;
use App\Services\Facts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BriefController extends Controller
{
    /**
     * Briefs still pending after this many seconds are considered stuck.
     */
    public const int STALE_AFTER = 180;

    public function create(): Response
    {
        return Inertia::render('brief/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country' => ['required', 'string', 'max:60'],
            'profession' => ['required', 'string', 'max:80'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'family' => ['required', 'in:single,couple,family'],
            'locale' => ['required', 'in:en,ar'],
        ]);

        $brief = Brief::create([...$data, 'user_id' => $request->user()?->id]);

        return to_route('briefs.show', $brief);
    }

    /**
     * Generate a pending brief. Called by the brief page, so the slow model call
     * never blocks a page load and needs no queue worker.
     */
    public function generate(Brief $brief, BriefGenerator $generator): JsonResponse
    {
        $claimed = Brief::whereKey($brief->id)
            ->where('status', Brief::PENDING)
            ->update(['status' => Brief::GENERATING]);

        if ($claimed) {
            // The edge proxy may time out (~20s) before the model finishes; keep going, the page polls.
            ignore_user_abort(true);
            set_time_limit(self::STALE_AFTER);
            $brief->generate($generator);
        }

        return response()->json(['status' => $brief->fresh()->status]);
    }

    public function show(Brief $brief, Facts $facts): Response
    {
        $status = $brief->status === Brief::GENERATING && $brief->updated_at->diffInSeconds() > self::STALE_AFTER
            ? Brief::FAILED
            : $brief->status;

        return Inertia::render('brief/Show', [
            'brief' => [
                'id' => $brief->public_id,
                'country' => $brief->country,
                'profession' => $brief->profession,
                'experience_years' => $brief->experience_years,
                'family' => $brief->family,
                'locale' => $brief->locale,
                'status' => $status,
                'content' => $brief->content,
                'sources' => $facts->citedSources($brief->content),
                'created_at' => $brief->created_at->toIso8601String(),
            ],
        ]);
    }
}
