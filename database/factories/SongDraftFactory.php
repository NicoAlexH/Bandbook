<?php

namespace Database\Factories;

use App\Actions\ParseSongChart;
use App\Models\SongDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SongDraft>
 */
class SongDraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $source = "[Verse]\n[G]Morning finds the [Em]open door";
        $chart = (new ParseSongChart)->parse($source);

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'original_source' => $source,
            'reviewed_source' => $chart['canonical'],
            'content_sections' => $chart['contents'],
            'section_occurrences' => $chart['occurrences'],
            'import_fingerprint' => hash('sha256', Str::uuid()->toString()),
        ];
    }
}
