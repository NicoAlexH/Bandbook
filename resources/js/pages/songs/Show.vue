<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Play, Music2 } from '@lucide/vue';
import SongMeta from '@/components/SongMeta.vue';
import { Button } from '@/components/ui/button';
import BandbookLayout from '@/layouts/BandbookLayout.vue';
import { index } from '@/routes/songs';
import { songPerformance } from '@/lib/songLinks';
import type { DemoSong } from '@/types/music';
defineProps<{ song: DemoSong }>();
</script>

<template>
    <BandbookLayout :song-slug="song.slug" :draft-id="song.draftId">
        <Head :title="`${song.title} · Bandbook`" />
        <div class="space-y-7">
            <Link
                :href="index()"
                class="inline-flex min-h-11 items-center gap-2 text-sm text-stone-500"
                ><ArrowLeft class="size-4" /> All songs</Link
            >
            <header>
                <p
                    class="mb-3 text-xs font-semibold tracking-widest text-[#64816e] uppercase"
                >
                    {{ song.mood }} · {{ song.duration }}
                </p>
                <h1
                    class="text-4xl leading-tight font-semibold tracking-tight [overflow-wrap:anywhere]"
                >
                    {{ song.title }}
                </h1>
                <p class="mt-2 mb-5 text-stone-500">{{ song.artist }}</p>
                <SongMeta :song="song" />
            </header>
            <Button
                as-child
                class="h-14 w-full rounded-xl bg-[#284f40] text-base text-white hover:bg-[#35634f]"
                ><Link :href="songPerformance(song)"
                    ><Play class="size-5" /> Start playing</Link
                ></Button
            >
            <aside class="rounded-xl border border-[#d9e3d4] bg-[#edf1e8] p-4">
                <h2 class="mb-2 flex items-center gap-2 text-sm font-semibold">
                    <Music2 class="size-4" /> Band notes
                </h2>
                <p class="text-sm leading-relaxed text-[#526657]">
                    {{ song.note }}
                </p>
            </aside>
            <section aria-labelledby="arrangement-title">
                <div class="mb-5 flex items-baseline justify-between gap-2">
                    <h2 id="arrangement-title" class="text-xl font-semibold">
                        The arrangement
                    </h2>
                    <span class="text-xs text-stone-500"
                        >{{ song.sections.length }} sections ·
                        {{ song.draftId ? 'Draft' : 'Demo' }}</span
                    >
                </div>
                <ol class="space-y-3">
                    <li
                        v-for="(section, position) in song.sections"
                        :key="section.id"
                        class="rounded-xl border border-black/8 bg-white p-4"
                    >
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs text-[#64816e]">{{
                                String(position + 1).padStart(2, '0')
                            }}</span>
                            <h3
                                class="min-w-0 flex-1 font-semibold [overflow-wrap:anywhere]"
                            >
                                {{ section.name }}
                            </h3>
                            <span
                                v-if="section.bars !== null"
                                class="shrink-0 text-xs text-stone-500"
                                >{{ section.bars }} bars</span
                            >
                        </div>
                        <p
                            class="mt-3 font-mono text-sm font-semibold [overflow-wrap:anywhere] text-[#52764f]"
                        >
                            {{
                                section.lines
                                    .map((line) => line.chord)
                                    .join('  /  ')
                            }}
                        </p>
                        <p
                            class="mt-2 text-sm leading-relaxed [overflow-wrap:anywhere] text-stone-500"
                        >
                            {{ section.lines[0].lyric }}
                        </p>
                    </li>
                </ol>
            </section>
            <section v-if="song.originalSource" class="space-y-3">
                <p v-if="song.sourceUrl" class="text-sm">
                    <a
                        :href="song.sourceUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center text-emerald-800 underline"
                        >Open original source</a
                    >
                </p>
                <details class="rounded-xl border border-black/10 bg-white p-4">
                    <summary class="cursor-pointer text-sm font-semibold">
                        Original imported text
                    </summary>
                    <pre
                        class="mt-4 font-mono text-sm [overflow-wrap:anywhere] whitespace-pre-wrap"
                        >{{ song.originalSource }}</pre>
                </details>
                <details class="rounded-xl border border-black/10 bg-white p-4">
                    <summary class="cursor-pointer text-sm font-semibold">
                        Reviewed ChordPro chart
                    </summary>
                    <pre
                        class="mt-4 font-mono text-sm [overflow-wrap:anywhere] whitespace-pre-wrap"
                        >{{ song.reviewedSource }}</pre>
                </details>
            </section>
        </div>
    </BandbookLayout>
</template>
