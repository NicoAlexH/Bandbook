<?php

use Inertia\Testing\AssertableInertia as Assert;

test('visitors can browse the fictional song library', function () {
    $this->get(route('songs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('songs/Index')
            ->has('songs', 3)
            ->where('songs.0.title', 'After the Rain')
            ->where('songs.1.slug', 'midnight-platform')
            ->where('songs.2.slug', 'hold-the-horizon'));
});

test('song screens expose ordered occurrences and musician parts', function (string $routeName, string $component) {
    $this->get(route($routeName, 'after-the-rain'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('song.key', 'G')
            ->where('song.bpm', 96)
            ->has('song.sections', 6)
            ->where('song.sections.2.id', 'chorus-1')
            ->where('song.sections.4.id', 'chorus-2')
            ->where('song.sections.1.lines.0.lyric', 'I left a light beside the door')
            ->has('song.sections.0.instructions.Singer')
            ->has('song.sections.0.instructions.Guitarist')
            ->has('song.sections.0.instructions.Bassist')
            ->has('song.sections.0.instructions.Pianist')
            ->has('song.sections.0.instructions.Percussionist'));
})->with([
    'song notes' => ['songs.show', 'songs/Show'],
    'performance' => ['songs.perform', 'songs/Perform'],
]);

test('unknown mock songs return not found', function (string $routeName) {
    $this->get(route($routeName, 'missing-song'))->assertNotFound();
})->with(['songs.show', 'songs.perform']);
