<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type Segment array{chord: string, lyric: string}
 * @phpstan-type Line array{chord: string, lyric: string, segments: list<Segment>}
 * @phpstan-type Content array{id: string, name: string, lines: list<Line>}
 * @phpstan-type Occurrence array{id: string, sectionId: string, name: string}
 * @phpstan-type Chart array{metadata: array<string, string>, contents: list<Content>, occurrences: list<Occurrence>, warnings: list<string>, canonical: string}
 */
class ParseSongChart
{
    private const CHORD = '~^(?:[A-G][#b♯♭]?(?:(?:maj|min|dim|aug|sus|add|omit|m|M|no|[0-9()+#b♯♭°øΔ-])*)(?:/[A-G][#b♯♭]?)?|N\.?C\.?)$~u';

    /** @return Chart */
    public function parse(string $source): array
    {
        if (strlen($source) > 100000 || ! mb_check_encoding($source, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $source)) {
            throw ValidationException::withMessages(['source' => 'Use a UTF-8 text chart smaller than 100 KB, without binary characters.']);
        }

        $rows = explode("\n", str_replace(["\r\n", "\r"], "\n", ltrim($source, "\xEF\xBB\xBF")));
        $metadata = [];
        $warnings = [];
        $rawSections = [];
        $current = ['name' => 'Song', 'lines' => []];
        $metadataNames = ['t' => 'title', 'title' => 'title', 'artist' => 'artist', 'key' => 'key', 'tempo' => 'bpm', 'capo' => 'capo', 'time' => 'meter'];
        $starts = ['soc' => 'Chorus', 'start_of_chorus' => 'Chorus', 'sov' => 'Verse', 'start_of_verse' => 'Verse', 'sob' => 'Bridge', 'start_of_bridge' => 'Bridge', 'chorus' => 'Chorus'];

        for ($i = 0; $i < count($rows); $i++) {
            if (count($rawSections) > 200) {
                throw ValidationException::withMessages(['source' => 'Import one song at a time (up to 100 sections).']);
            }
            $row = $this->expandTabs($rows[$i]);
            $trimmed = trim($row);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $heading = null;
            if (preg_match('/^\{([a-z_]+)(?:\s*:\s*|\s+)?(.*?)\}$/i', $trimmed, $directive)) {
                $name = strtolower($directive[1]);
                $value = trim($directive[2]);
                if (isset($metadataNames[$name])) {
                    $metadata[$metadataNames[$name]] = $value;

                    continue;
                }
                if (isset($starts[$name])) {
                    $heading = $value ?: $starts[$name];
                    if (preg_match('/^label=["\'](.*)["\']$/', $heading, $label)) {
                        $heading = $label[1];
                    }
                } elseif (in_array($name, ['eoc', 'eov', 'eob', 'end_of_chorus', 'end_of_verse', 'end_of_bridge'], true)) {
                    $rawSections[] = $current;
                    $current = ['name' => 'Song', 'lines' => []];

                    continue;
                } else {
                    $warnings[] = 'Line '.($i + 1).": {$trimmed} is retained in the original, but not interpreted. Add any needed instruction to the chart before saving.";

                    continue;
                }
            } elseif (preg_match('/^\[([^\[\]]+)\]$/u', $trimmed, $match) && ! $this->isChord($match[1])) {
                $heading = $match[1];
            }

            if ($heading !== null) {
                if ($current['lines'] !== [] || $current['name'] !== 'Song') {
                    $rawSections[] = $current;
                }
                $current = ['name' => $heading, 'lines' => []];

                continue;
            }

            if (preg_match('/^[eBGDAE]\|[-0-9hxpb~|]+/i', $trimmed)) {
                $warnings[] = 'Guitar tablature was kept as text. Fret notation and rhythm are not interpreted in this importer.';
            }

            $chords = $this->chordRow($row);
            if ($chords !== []) {
                $next = isset($rows[$i + 1]) ? $this->expandTabs($rows[$i + 1]) : '';
                $hasLyrics = trim($next) !== '' && ! preg_match('/^\s*[\[{#]/', $next) && $this->chordRow($next) === [];
                $lyric = $hasLyrics ? $next : '';
                $segments = [];
                if ($chords[0]['position'] > 0 && $lyric !== '') {
                    $segments[] = ['chord' => '', 'lyric' => mb_substr($lyric, 0, $chords[0]['position'])];
                }
                foreach ($chords as $j => $chord) {
                    $end = $chords[$j + 1]['position'] ?? max(mb_strlen($lyric), $chord['position']);
                    $segments[] = ['chord' => $chord['chord'], 'lyric' => mb_substr($lyric, $chord['position'], $end - $chord['position'])];
                }
                $current['lines'][] = $this->line($segments);
                if ($hasLyrics) {
                    $i++;
                }
            } else {
                $segments = [];
                $offset = 0;
                $chord = '';
                preg_match_all('/\[([^\[\]]+)\]/u', $row, $matches, PREG_OFFSET_CAPTURE);
                foreach ($matches[0] as $j => [$token, $position]) {
                    $candidate = $matches[1][$j][0];
                    if (! $this->isChord($candidate)) {
                        $warnings[] = 'Unrecognized bracketed text was preserved as lyrics. Check the preview for unsupported chords or instructions.';

                        continue;
                    }
                    $text = substr($row, $offset, $position - $offset);
                    if ($text !== '' || $chord !== '') {
                        $segments[] = ['chord' => $chord, 'lyric' => $text];
                    }
                    $chord = $candidate;
                    $offset = $position + strlen($token);
                }
                $segments[] = ['chord' => $chord, 'lyric' => substr($row, $offset)];
                $current['lines'][] = $this->line($segments);
            }
        }

        $rawSections[] = $current;
        $contents = [];
        $occurrences = [];
        foreach ($rawSections as $raw) {
            if ($raw['lines'] === [] && $raw['name'] === 'Song') {
                continue;
            }
            $existing = null;
            foreach (array_reverse($contents) as $content) {
                if (strcasecmp($content['name'], $raw['name']) === 0 && ($raw['lines'] === [] || $content['lines'] === $raw['lines'])) {
                    $existing = $content;
                    break;
                }
            }
            if ($existing === null && $raw['lines'] === []) {
                $warnings[] = "Empty section [{$raw['name']}] has no earlier content to repeat and was omitted. Add its lyrics or chords before saving.";

                continue;
            }
            if ($existing === null) {
                $existing = ['id' => 'section-'.(count($contents) + 1), ...$raw];
                $contents[] = $existing;
            }
            $occurrences[] = ['id' => 'occurrence-'.(count($occurrences) + 1), 'sectionId' => $existing['id'], 'name' => $raw['name']];
        }

        if ($contents === []) {
            throw ValidationException::withMessages(['source' => 'Add at least one lyric or chord line before previewing.']);
        }
        if (count($occurrences) > 100 || array_sum(array_map(fn (array $content): int => count($content['lines']), $contents)) > 1500) {
            throw ValidationException::withMessages(['source' => 'Import one song at a time (up to 100 sections and 1,500 lines).']);
        }

        $canonical = [];
        foreach ($occurrences as $occurrence) {
            $content = collect($contents)->firstWhere('id', $occurrence['sectionId']);
            $canonical[] = '['.$occurrence['name'].']';
            foreach ($content['lines'] as $line) {
                $canonical[] = implode('', array_map(fn (array $segment): string => ($segment['chord'] !== '' ? '['.$segment['chord'].']' : '').$segment['lyric'], $line['segments']));
            }
            $canonical[] = '';
        }

        return ['metadata' => $metadata, 'contents' => $contents, 'occurrences' => $occurrences, 'warnings' => array_values(array_unique($warnings)), 'canonical' => trim(implode("\n", $canonical))];
    }

    private function isChord(string $text): bool
    {
        return preg_match(self::CHORD, $text) === 1;
    }

    private function expandTabs(string $line): string
    {
        $result = '';
        $column = 0;
        foreach (mb_str_split($line) as $character) {
            $width = $character === "\t" ? 8 - $column % 8 : 1;
            $result .= $character === "\t" ? str_repeat(' ', $width) : $character;
            $column += $width;
        }

        return $result;
    }

    /** @return list<array{chord: string, position: int}> */
    private function chordRow(string $row): array
    {
        preg_match_all('/\S+/u', $row, $matches, PREG_OFFSET_CAPTURE);
        $chords = [];
        foreach ($matches[0] as [$token, $offset]) {
            if (preg_match('/^[|:%]+$/', $token)) {
                continue;
            }
            if (! $this->isChord($token)) {
                return [];
            }
            $chords[] = ['chord' => $token, 'position' => mb_strlen(substr($row, 0, $offset))];
        }

        return $chords;
    }

    /** @param list<Segment> $segments
     * @return Line
     */
    private function line(array $segments): array
    {
        return ['chord' => implode(' · ', array_filter(array_column($segments, 'chord'))), 'lyric' => implode('', array_column($segments, 'lyric')), 'segments' => $segments];
    }
}
