<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Radio, CornerUpLeft } from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import RoleSelector from '@/components/RoleSelector.vue';
import SectionMusic from '@/components/SectionMusic.vue';
import SectionNavigator from '@/components/SectionNavigator.vue';
import SongMeta from '@/components/SongMeta.vue';
import { Button } from '@/components/ui/button';
import { usePerformance } from '@/composables/usePerformance';
import BandbookLayout from '@/layouts/BandbookLayout.vue';
import { songDetail } from '@/lib/songLinks';
import type { DemoSong, MusicianRole } from '@/types/music';

const props = defineProps<{ song: DemoSong }>();
const role = ref<MusicianRole>('Singer');
const {
    activeIndex,
    leaderIndex,
    following,
    navigate,
    setFollowing,
    advanceLeader,
} = usePerformance(props.song.sections.length);
const section = computed(() => props.song.sections[activeIndex.value]);

async function revealSection() {
    await nextTick();
    document
        .getElementById('current-section')
        ?.scrollIntoView({ block: 'start' });
}

function browseSection(index: number) {
    navigate(index);
    void revealSection();
}

function rejoinLeader() {
    setFollowing(true);
    void revealSection();
}
</script>

<template>
    <BandbookLayout performance :song-slug="song.slug" :draft-id="song.draftId">
        <Head :title="`${song.title} · Performance`" />
        <div class="space-y-6" :class="{ 'pb-20': !following }">
            <header>
                <Link
                    :href="songDetail(song)"
                    class="mb-3 inline-flex min-h-11 items-center gap-2 text-sm text-stone-400"
                    ><ArrowLeft class="size-4" /> Song notes</Link
                >
                <h1
                    class="mb-3 text-3xl font-semibold tracking-tight [overflow-wrap:anywhere]"
                >
                    {{ song.title }}
                </h1>
                <SongMeta :song="song" />
                <p v-if="song.draftId" class="mt-3 text-xs text-amber-200">
                    Private draft · not approved for performance
                </p>
            </header>
            <RoleSelector v-model="role" />
            <section aria-label="Section following" class="space-y-3">
                <div
                    class="grid grid-cols-2 gap-1 rounded-xl bg-white/5 p-1"
                    role="group"
                    aria-label="Follow mode"
                >
                    <Button
                        class="h-11 rounded-lg"
                        :class="
                            following
                                ? 'bg-lime-200 text-emerald-950 hover:bg-lime-100'
                                : 'bg-transparent text-stone-400 hover:bg-white/10'
                        "
                        :aria-pressed="following"
                        @click="setFollowing(true)"
                        ><Radio /> Follow</Button
                    >
                    <Button
                        class="h-11 rounded-lg"
                        :class="
                            !following
                                ? 'bg-white/15 text-white hover:bg-white/20'
                                : 'bg-transparent text-stone-400 hover:bg-white/10'
                        "
                        :aria-pressed="!following"
                        @click="setFollowing(false)"
                        >Free</Button
                    >
                </div>
                <p
                    class="text-sm [overflow-wrap:anywhere] text-stone-400"
                    role="status"
                >
                    {{
                        following
                            ? 'Following the demo leader'
                            : 'Exploring on your own'
                    }}
                    <span aria-hidden="true">·</span> Leader:
                    {{ song.sections[leaderIndex].name }}
                </p>
                <div
                    v-if="!following"
                    class="fixed inset-x-5 bottom-[calc(5.5rem+env(safe-area-inset-bottom))] z-40 mx-auto max-w-[728px]"
                >
                    <Button
                        class="h-14 w-full rounded-xl bg-lime-200 text-base font-semibold text-emerald-950 shadow-lg hover:bg-lime-100"
                        @click="rejoinLeader"
                        ><CornerUpLeft class="size-5" /> Rejoin leader</Button
                    >
                </div>
            </section>
            <section
                aria-labelledby="current-section"
                class="border-y border-white/10 py-7"
            >
                <div class="mb-6 flex items-start justify-between gap-3">
                    <div>
                        <p
                            class="mb-2 text-xs font-semibold tracking-[0.18em] text-lime-200 uppercase"
                        >
                            {{ section.energy
                            }}<template v-if="section.bars !== null">
                                · {{ section.bars }} bars</template
                            >
                        </p>
                        <h2
                            id="current-section"
                            class="scroll-mt-5 text-3xl font-semibold tracking-tight [overflow-wrap:anywhere]"
                            aria-live="polite"
                        >
                            {{ section.name }}
                        </h2>
                    </div>
                    <span class="shrink-0 pt-1 font-mono text-sm text-stone-500"
                        >{{ String(activeIndex + 1).padStart(2, '0') }} /
                        {{
                            String(song.sections.length).padStart(2, '0')
                        }}</span
                    >
                </div>
                <SectionMusic :section="section" :role="role" />
            </section>
            <SectionNavigator
                :sections="song.sections"
                :active-index="activeIndex"
                @navigate="browseSection"
            />
            <p class="text-sm text-stone-400">
                {{
                    activeIndex < song.sections.length - 1
                        ? `Up next: ${song.sections[activeIndex + 1].name}`
                        : 'End of song. Let the last note settle.'
                }}
            </p>
            <details
                class="rounded-xl border border-white/10 px-4 text-sm text-stone-400"
            >
                <summary class="cursor-pointer py-4">
                    Try the local leader demo
                </summary>
                <p class="mb-3 leading-relaxed">
                    Advance the leader to try Follow and Free. This demo only
                    changes this screen; no other musicians are connected.
                </p>
                <Button
                    variant="outline"
                    class="mb-4 min-h-12 w-full border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white"
                    :disabled="leaderIndex === song.sections.length - 1"
                    @click="advanceLeader"
                    >Advance demo leader</Button
                >
            </details>
        </div>
    </BandbookLayout>
</template>
