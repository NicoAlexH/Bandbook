<?php

namespace App\Http\Controllers;

use App\Models\SongDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class SongPrototypeController extends Controller
{
    public function index(Request $request): Response
    {
        $drafts = $request->user() === null ? [] : SongDraft::query()
            ->whereBelongsTo($request->user())->latest('id')->get()
            ->map(fn (SongDraft $draft): array => $draft->songData())->all();

        return Inertia::render('songs/Index', ['songs' => [...$this->songs(), ...$drafts]]);
    }

    public function show(string $song): Response
    {
        return Inertia::render('songs/Show', ['song' => $this->findSong($song)]);
    }

    public function perform(string $song): Response
    {
        return Inertia::render('songs/Perform', ['song' => $this->findSong($song)]);
    }

    /** @return list<array<string, mixed>> */
    private function songs(): array
    {
        return array_values(File::json(resource_path('js/lib/demo-songs.json')));
    }

    /** @return array<string, mixed> */
    private function findSong(string $slug): array
    {
        $song = collect($this->songs())->firstWhere('slug', $slug);

        abort_if($song === null, 404);

        return $song;
    }
}
