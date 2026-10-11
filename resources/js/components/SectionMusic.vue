<script setup lang="ts">
import ChordLine from '@/components/ChordLine.vue';
import type { MusicianRole, SongSection } from '@/types/music';
defineProps<{ section: SongSection; role: MusicianRole }>();
</script>

<template>
    <div class="space-y-7">
        <p
            class="border-l-2 border-lime-200/70 pl-4 text-base leading-relaxed text-stone-300"
        >
            {{ section.instructions[role] }}
        </p>
        <div
            v-if="role !== 'Percussionist' || section.bars === null"
            class="space-y-6"
        >
            <ChordLine
                v-for="(line, index) in section.lines"
                :key="index"
                :line="line"
                :lyrics-only="role === 'Singer' || role === 'Percussionist'"
                :bass="role === 'Bassist'"
                dark
            />
        </div>
        <div v-else class="rounded-2xl border border-white/10 p-5">
            <p
                class="mb-4 text-xs font-semibold tracking-widest text-stone-400 uppercase"
            >
                Count · steady pulse
            </p>
            <div
                class="grid grid-cols-4 gap-2 text-center font-mono text-3xl font-semibold"
            >
                <span
                    v-for="beat in 4"
                    :key="beat"
                    class="rounded-lg py-3"
                    :class="
                        beat === 1
                            ? 'bg-lime-200 text-emerald-950'
                            : 'bg-white/5'
                    "
                    >{{ beat }}</span
                >
            </div>
            <p class="mt-4 text-sm text-stone-400">
                {{ section.bars }} bars · {{ section.energy }}
            </p>
            <p class="mt-4 text-lg text-stone-200">
                Vocal cue: “{{ section.lines[0].lyric }}”
            </p>
        </div>
    </div>
</template>
