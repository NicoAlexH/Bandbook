<?php

namespace App\Models;

use Database\Factories\SongDraftFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @phpstan-import-type Content from \App\Actions\ParseSongChart
 * @phpstan-import-type Occurrence from \App\Actions\ParseSongChart
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $artist
 * @property string|null $musical_key
 * @property int|null $bpm
 * @property int|null $capo
 * @property string|null $meter
 * @property string|null $source_url
 * @property string $original_source
 * @property string $reviewed_source
 * @property list<Content> $content_sections
 * @property list<Occurrence> $section_occurrences
 */
#[Fillable(['user_id', 'title', 'artist', 'musical_key', 'bpm', 'capo', 'meter', 'source_url', 'original_source', 'reviewed_source', 'content_sections', 'section_occurrences', 'import_fingerprint'])]
class SongDraft extends Model
{
    /** @use HasFactory<SongDraftFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['content_sections' => 'array', 'section_occurrences' => 'array', 'bpm' => 'integer', 'capo' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, mixed> */
    public function songData(): array
    {
        $contents = collect($this->content_sections)->keyBy('id');

        return [
            'draftId' => $this->id,
            'slug' => 'draft-'.$this->id,
            'title' => $this->title,
            'artist' => $this->artist ?? 'Artist not set',
            'key' => $this->musical_key ?? 'Not set',
            'bpm' => $this->bpm,
            'capo' => $this->capo,
            'meter' => $this->meter ?? 'Not set',
            'mood' => 'Private draft',
            'duration' => 'Imported',
            'note' => 'Imported draft — not an approved performance version. Instrument-specific instructions have not been added.',
            'sourceUrl' => $this->source_url,
            'originalSource' => $this->original_source,
            'reviewedSource' => $this->reviewed_source,
            'sections' => array_map(fn (array $occurrence): array => [
                'id' => $occurrence['id'],
                'name' => $occurrence['name'],
                'bars' => null,
                'energy' => 'Imported',
                'lines' => $contents[$occurrence['sectionId']]['lines'],
                'instructions' => array_fill_keys(['Singer', 'Guitarist', 'Bassist', 'Pianist', 'Percussionist'], 'No part-specific instructions yet. Follow the chart and agree your part with the band.'),
            ], $this->section_occurrences),
        ];
    }
}
