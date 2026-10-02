<?php

namespace App\Http\Controllers;

use App\Models\Brief;
use App\Models\ScamCheck;
use App\Services\Facts;
use App\Services\ScamChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScamCheckController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('check/Create', [
            'locale' => $request->query('lang') === 'ar' ? 'ar' : 'en',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'locale' => ['required', 'in:en,ar'],
        ]);

        $check = ScamCheck::create([...$data, 'user_id' => $request->user()?->id]);

        return to_route('checks.show', $check);
    }

    /**
     * Run a pending check. Called by the result page, like brief generation.
     */
    public function run(ScamCheck $check, ScamChecker $checker): JsonResponse
    {
        if (BriefController::claim($check)) {
            // The edge proxy may time out (~20s) before the model finishes; keep going, the page polls.
            ignore_user_abort(true);
            set_time_limit(BriefController::STALE_AFTER);
            $check->check($checker);
        }

        return response()->json(['status' => $check->fresh()->status]);
    }

    public function show(ScamCheck $check, Facts $facts): Response
    {
        $status = $check->status === Brief::GENERATING && $check->updated_at->diffInSeconds() > BriefController::STALE_AFTER
            ? Brief::FAILED
            : $check->status;

        return Inertia::render('check/Show', [
            'locale' => $check->locale,
            'check' => [
                'id' => $check->public_id,
                'message' => $check->message,
                'locale' => $check->locale,
                'status' => $status,
                'content' => $check->content,
                'sources' => $facts->citedSources($check->content),
                'created_at' => $check->created_at->toIso8601String(),
            ],
        ]);
    }
}
