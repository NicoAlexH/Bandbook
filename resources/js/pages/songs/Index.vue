<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, ChevronRight, Search, AudioLines } from '@lucide/vue';
import { computed, ref } from 'vue';
import SongMeta from '@/components/SongMeta.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import BandbookLayout from '@/layouts/BandbookLayout.vue';
import { importMethod } from '@/routes/songs';
import { songDetail, songPerformance } from '@/lib/songLinks';
import type { DemoSong } from '@/types/music';

const props = defineProps<{ songs: DemoSong[] }>();
const search = ref('');
const filteredSongs = computed(() =>
    props.songs.filter((song) =>
        `${song.title} ${song.artist} ${song.mood}`
            .toLowerCase()
            .includes(search.value.trim().toLowerCase()),
    ),
);
</script>

<template>
    <BandbookLayout>
        <Head title="Songs · Bandbook" />
        <div class="space-y-7">
            <div>
                <p
                    class="mb-2 text-xs font-semibold tracking-[0.18em] text-[#64816e] uppercase"
                >
                    Your shared songbook
                </p>
                <h1 class="text-4xl font-semibold tracking-tight">
                    Good music.<br />Same page.
                </h1>
                <p class="mt-3 text-base leading-relaxed text-stone-500">
                    Find your song. Pick your part. Play together.
                </p>
            </div>
            <section
                class="rounded-2xl bg-[#284f40] p-5 text-white"
                aria-labelledby="rehearsal-title"
            >
                <div
                    class="mb-5 flex items-center gap-2 text-xs font-medium text-lime-200"
                >
                    <AudioLines class="size-4" /> READY TO REHEARSE
                </div>
                <h2
                    id="rehearsal-title"
                    class="text-2xl font-semibold tracking-tight"
                >
                    {{ songs[0].title }}
                </h2>
                <p class="mt-1 text-sm text-emerald-100/70">
                    {{ songs[0].artist }} · {{ songs[0].key }} ·
                    {{ songs[0].bpm }} BPM
                </p>
                <Button
                    as-child
                    class="mt-5 h-12 w-full justify-between rounded-xl bg-lime-200 px-4 text-emerald-950 hover:bg-lime-100"
                    ><Link :href="songPerformance(songs[0])"
                        >Open performance view
                        <ArrowUpRight class="size-5" /></Link
                ></Button>
            </section>
            <section aria-labelledby="library-title" class="space-y-4">
                <Button
                    as-child
                    class="h-12 w-full rounded-xl bg-[#284f40] text-white hover:bg-[#35634f]"
                    ><Link :href="importMethod()">Import a song</Link></Button
                >
                <div class="flex items-baseline justify-between">
                    <h2
                        id="library-title"
                        class="text-xl font-semibold tracking-tight"
                    >
                        Songs
                    </h2>
                    <span class="text-sm text-stone-500"
                        >{{ songs.length }} in your library</span
                    >
                </div>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-4 left-4 size-4 text-stone-400"
                    />
                    <Input
                        v-model="search"
                        aria-label="Search songs"
                        placeholder="Search songs or artists…"
                        class="h-12 rounded-xl border-black/10 bg-white pl-11 text-base text-[#20352d] shadow-none placeholder:text-stone-400 md:text-base"
                    />
                </div>
                <ul class="divide-y divide-black/8">
                    <li v-for="(song, index) in filteredSongs" :key="song.slug">
                        <Link
                            :href="songDetail(song)"
                            class="group flex min-w-0 items-center gap-3 rounded-xl py-5 focus-visible:outline-2 focus-visible:outline-emerald-700"
                        >
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#e6ede3] font-mono text-sm text-[#64816e]"
                                >{{ String(index + 1).padStart(2, '0') }}</span
                            >
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="text-lg font-semibold tracking-tight break-words group-hover:text-emerald-700"
                                >
                                    {{ song.title }}
                                </h3>
                                <p class="mb-2 text-xs text-stone-500">
                                    {{ song.mood }} · {{ song.duration }}
                                </p>
                                <SongMeta :song="song" />
                            </div>
                            <ChevronRight
                                class="size-4 shrink-0 text-stone-400"
                            />
                        </Link>
                    </li>
                </ul>
                <p
                    v-if="filteredSongs.length === 0"
                    role="status"
                    class="rounded-xl border border-dashed border-black/15 p-6 text-center text-stone-500"
                >
                    No songs found. Try a different title or artist.
                </p>
            </section>
            <p class="text-center text-xs leading-relaxed text-stone-500">
                The three demo songs are fictional.<br />Imported drafts are
                private to your account.
            </p>
        </div>
    </BandbookLayout>
</template>
