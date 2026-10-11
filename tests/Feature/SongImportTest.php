<?php

use App\Actions\ParseSongChart;
use App\Models\SongDraft;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('importing requires an account', function () {
    $this->get(route('songs.import'))->assertRedirect(route('login'));
    $this->postJson(route('songs.preview'), ['source' => '[G]Hello'])->assertUnauthorized();
    $this->postJson(route('song-drafts.store'), [])->assertUnauthorized();
    $this->assertDatabaseCount('song_drafts', 0);
});

test('the import screen is available to a signed in member', function () {
    $this->actingAs(User::factory()->create())->get(route('songs.import'))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('songs/Import'));
});

test('a preview converts aligned chords and preserves the exact original text', function () {
    $source = "  G       Em\r\n  Morning light\r\n";

    $this->actingAs(User::factory()->create())->postJson(route('songs.preview'), ['source' => $source])
        ->assertOk()
        ->assertJsonPath('originalSource', $source)
        ->assertJsonPath('contents.0.lines.0.lyric', '  Morning light')
        ->assertJsonPath('contents.0.lines.0.segments.1.chord', 'G')
        ->assertJsonPath('contents.0.lines.0.segments.1.lyric', 'Morning ')
        ->assertJsonPath('contents.0.lines.0.segments.2.chord', 'Em')
        ->assertJsonPath('contents.0.lines.0.segments.2.lyric', 'light');

    $this->assertDatabaseCount('song_drafts', 0);
});

test('ChordPro metadata and repeated sections retain independent occurrence identities', function () {
    $chart = (new ParseSongChart)->parse("{title: Morning}\n{artist: Our Band}\n{key: Bb}\n{tempo: 96}\n{capo: 2}\n{time: 6/8}\n{soc}\n[Bb]Hello [F/A]again\n{eoc}\n{chorus}");

    expect($chart['metadata'])->toBe(['title' => 'Morning', 'artist' => 'Our Band', 'key' => 'Bb', 'bpm' => '96', 'capo' => '2', 'meter' => '6/8']);
    expect($chart['contents'])->toHaveCount(1);
    expect($chart['occurrences'])->toBe([
        ['id' => 'occurrence-1', 'sectionId' => 'section-1', 'name' => 'Chorus'],
        ['id' => 'occurrence-2', 'sectionId' => 'section-1', 'name' => 'Chorus'],
    ]);
    expect($chart['contents'][0]['lines'][0]['segments'])->toBe([
        ['chord' => 'Bb', 'lyric' => 'Hello '], ['chord' => 'F/A', 'lyric' => 'again'],
    ]);
});

test('instrumental chord rows are kept separate and unicode lyrics survive conversion', function () {
    $chart = (new ParseSongChart)->parse("[Intro]\nG D\nEm C\n\n[Verse]\nC   G\nÉté à Paris\n[F#m7]One [Bbmaj7]two [N.C.]rest");

    expect($chart['contents'][0]['lines'])->toHaveCount(2);
    expect($chart['contents'][0]['lines'][0]['lyric'])->toBe('');
    expect($chart['contents'][1]['lines'][0]['segments'])->toBe([
        ['chord' => 'C', 'lyric' => 'Été '], ['chord' => 'G', 'lyric' => 'à Paris'],
    ]);
    expect($chart['contents'][1]['lines'][1]['chord'])->toBe('F#m7 · Bbmaj7 · N.C.');
});

test('unknown directives and unresolved repeats produce review warnings', function () {
    $chart = (new ParseSongChart)->parse("{define: C frets x 3 2 0 1 0}\n[Bridge]\n[Verse]\n[G]Hello [mystery]world");

    expect($chart['warnings'])->toHaveCount(3);
    expect($chart['contents'][0]['lines'][0]['lyric'])->toBe('Hello [mystery]world');
});

test('unusable and oversized charts are rejected', function (string $source) {
    $this->actingAs(User::factory()->create())->postJson(route('songs.preview'), ['source' => $source])
        ->assertUnprocessable()->assertJsonValidationErrors('source');
    $this->assertDatabaseCount('song_drafts', 0);
})->with([
    'blank' => '   ',
    'only metadata' => '{title: Empty}',
    'binary' => "some\0data",
    'too many bytes' => str_repeat('a', 100001),
    'too many sections' => implode("\n", array_fill(0, 101, "[Verse]\n[G]Hello")),
]);

test('a reviewed import saves a private draft and does not duplicate on retry', function () {
    $user = User::factory()->create();
    $source = "{title: First title}\n[Chorus]\n[G]Morning [D/F#]light\n[Chorus]\n";
    $preview = $this->actingAs($user)->postJson(route('songs.preview'), ['source' => $source])->assertOk()->json();
    $payload = [
        'title' => 'Reviewed title', 'artist' => 'Our band', 'key' => 'G', 'bpm' => 96, 'capo' => 2, 'meter' => '4/4',
        'source_url' => 'https://example.com/chart', 'source' => $preview['canonical'], 'original_source' => $preview['originalSource'],
        'review_token' => $preview['reviewToken'], 'confirmed' => true, 'user_id' => 9999,
    ];

    $response = $this->post(route('song-drafts.store'), $payload);

    $draft = SongDraft::query()->sole();
    $response->assertRedirect(route('song-drafts.show', $draft));
    expect($draft->user_id)->toBe($user->id);
    expect($draft->title)->toBe('Reviewed title');
    expect($draft->original_source)->toBe($source);
    expect($draft->content_sections)->toHaveCount(1);
    expect($draft->section_occurrences)->toHaveCount(2);
    $this->get(route('song-drafts.perform', $draft))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('songs/Perform')
            ->where('song.draftId', $draft->id)->where('song.capo', 2)->where('song.sections.0.bars', null));
    $this->get(route('songs.index'))->assertInertia(fn (Assert $page) => $page->has('songs', 4)->where('songs.3.title', 'Reviewed title'));
    $this->post(route('song-drafts.store'), $payload)->assertRedirect(route('song-drafts.show', $draft));
    $this->assertDatabaseCount('song_drafts', 1);
});

test('saving requires a current preview and explicit review confirmation', function (string $change) {
    $this->actingAs(User::factory()->create());
    $preview = $this->postJson(route('songs.preview'), ['source' => '[G]Morning'])->assertOk()->json();
    $payload = ['title' => 'Morning', 'source' => $preview['canonical'], 'original_source' => $preview['originalSource'], 'review_token' => $preview['reviewToken'], 'confirmed' => true];
    $payload[$change] = match ($change) {
        'source' => '[Em]Changed', 'original_source' => 'Different original', 'review_token' => 'invalid', 'confirmed' => false,
    };

    $this->postJson(route('song-drafts.store'), $payload)->assertUnprocessable()
        ->assertJsonValidationErrors($change === 'confirmed' ? 'confirmed' : 'source');
    $this->assertDatabaseCount('song_drafts', 0);
})->with(['source', 'original_source', 'review_token', 'confirmed']);

test('invalid musical details and unsafe source links cannot be saved', function () {
    $this->actingAs(User::factory()->create())->postJson(route('song-drafts.store'), [
        'title' => '', 'source' => '[G]Hello', 'bpm' => 900, 'capo' => -1, 'meter' => 'fast', 'source_url' => 'javascript:alert(1)',
        'review_token' => 'invalid', 'confirmed' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'bpm', 'capo', 'meter', 'source_url']);
    $this->assertDatabaseCount('song_drafts', 0);
});

test('other members cannot discover or open a private draft', function () {
    $draft = SongDraft::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('songs.index'))->assertInertia(fn (Assert $page) => $page->has('songs', 3));
    $this->get(route('song-drafts.show', $draft))->assertNotFound();
    $this->get(route('song-drafts.perform', $draft))->assertNotFound();
});

test('review tokens are bound to the importing account', function () {
    $this->actingAs(User::factory()->create());
    $preview = $this->postJson(route('songs.preview'), ['source' => '[G]Hello'])->assertOk()->json();
    $this->actingAs(User::factory()->create());

    $this->postJson(route('song-drafts.store'), ['title' => 'Hello', 'source' => $preview['canonical'], 'original_source' => $preview['originalSource'], 'review_token' => $preview['reviewToken'], 'confirmed' => true])
        ->assertUnprocessable()->assertJsonValidationErrors('source');
    $this->assertDatabaseCount('song_drafts', 0);
});
