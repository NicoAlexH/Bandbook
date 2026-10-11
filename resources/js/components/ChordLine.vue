<script setup lang="ts">
import { computed } from 'vue';
import type { MusicLine } from '@/types/music';

const props = withDefaults(
    defineProps<{
        line: MusicLine;
        lyricsOnly?: boolean;
        bass?: boolean;
        dark?: boolean;
    }>(),
    { lyricsOnly: false, bass: false, dark: false },
);
const segments = computed(
    () =>
        props.line.segments ?? [
            { chord: props.line.chord, lyric: props.line.lyric },
        ],
);

function displayedChord(chord: string) {
    if (!props.bass) return chord;
    return (
        chord.match(/\/([A-G][#b♯♭]?)$/)?.[1] ??
        chord.match(/^[A-G][#b♯♭]?/)?.[0] ??
        chord
    );
}
</script>

<template>
    <p
        v-if="lyricsOnly"
        class="text-[1.75rem] leading-snug font-medium tracking-tight [overflow-wrap:anywhere] whitespace-pre-wrap sm:text-4xl"
    >
        {{ line.lyric || 'Instrumental' }}
    </p>
    <p
        v-else
        class="text-xl leading-relaxed [overflow-wrap:anywhere] whitespace-pre-wrap"
    >
        <span
            v-for="(segment, index) in segments"
            :key="index"
            class="inline-flex max-w-full flex-col align-bottom"
            :class="{ 'min-w-10 pr-3': !segment.lyric }"
            ><span
                class="min-h-8 font-mono text-lg font-semibold"
                :class="dark ? 'text-lime-200' : 'text-emerald-800'"
                >{{ displayedChord(segment.chord) || '\u00a0' }}</span
            ><span>{{ segment.lyric || '\u00a0' }}</span></span
        >
    </p>
</template>
