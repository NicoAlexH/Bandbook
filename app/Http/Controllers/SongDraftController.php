<?php

namespace App\Http\Controllers;

use App\Actions\ParseSongChart;
use App\Http\Requests\ImportSongRequest;
use App\Models\SongDraft;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SongDraftController extends Controller
{
    public function store(ImportSongRequest $request, ParseSongChart $parser): RedirectResponse
    {
        try {
            $review = json_decode(Crypt::decryptString($request->string('review_token')->toString()), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            throw ValidationException::withMessages(['source' => 'Preview this chart again before saving.']);
        }

        $source = $request->string('source')->toString();
        $original = $request->string('original_source')->toString();
        if (($review['user'] ?? null) !== $request->user()->id || ($review['source'] ?? '') !== hash('sha256', $source) || ($review['original'] ?? '') !== hash('sha256', $original)) {
            throw ValidationException::withMessages(['source' => 'The chart changed after preview. Preview it again before saving.']);
        }

        $chart = $parser->parse($source);
        $attributes = $request->safe()->only(['title', 'artist', 'bpm', 'capo', 'meter', 'source_url']);
        $fingerprint = hash('sha256', $request->string('review_token')->toString());
        $draft = SongDraft::query()->firstOrCreate(['import_fingerprint' => $fingerprint], [
            ...$attributes,
            'musical_key' => $request->input('key'),
            'original_source' => $original,
            'reviewed_source' => $source,
            'content_sections' => $chart['contents'],
            'section_occurrences' => $chart['occurrences'],
            'user_id' => $request->user()->id,
        ]);

        return to_route('song-drafts.show', $draft);
    }

    public function show(SongDraft $songDraft): Response
    {
        Gate::authorize('view', $songDraft);

        return Inertia::render('songs/Show', ['song' => $songDraft->songData()]);
    }

    public function perform(SongDraft $songDraft): Response
    {
        Gate::authorize('view', $songDraft);

        return Inertia::render('songs/Perform', ['song' => $songDraft->songData()]);
    }
}
