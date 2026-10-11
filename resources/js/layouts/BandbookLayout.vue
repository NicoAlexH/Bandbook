<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AudioLines, Library, Music2 } from '@lucide/vue';
import { index } from '@/routes/songs';
import { songPerformance } from '@/lib/songLinks';

withDefaults(
    defineProps<{
        performance?: boolean;
        songSlug?: string;
        draftId?: number;
    }>(),
    {
        performance: false,
        songSlug: 'after-the-rain',
    },
);
</script>

<template>
    <div
        :class="
            performance
                ? 'dark bg-[#101916] text-stone-100'
                : 'bg-[#f6f7f2] text-[#20352d]'
        "
        class="min-h-dvh pb-[calc(5.5rem+env(safe-area-inset-bottom))] selection:bg-lime-200 selection:text-emerald-950"
    >
        <a
            href="#main-content"
            class="sr-only fixed top-2 left-2 z-50 rounded-lg bg-white p-3 text-black focus:not-sr-only"
            >Skip to music</a
        >
        <header
            class="border-b"
            :class="performance ? 'border-white/10' : 'border-black/10'"
        >
            <div
                class="mx-auto flex max-w-3xl items-center justify-between px-5 py-4"
            >
                <Link
                    :href="index()"
                    class="flex min-h-11 items-center gap-2.5 text-xl font-bold tracking-tight"
                    aria-label="Bandbook songs"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-[#284f40] text-lime-200"
                        ><Music2 class="size-5"
                    /></span>
                    bandbook<span class="text-emerald-500">.</span>
                </Link>
                <span
                    class="rounded-full border px-2.5 py-1 text-xs font-medium"
                    :class="
                        performance
                            ? 'border-white/15 text-stone-400'
                            : 'border-black/10 text-stone-500'
                    "
                    >Demo band</span
                >
            </div>
        </header>
        <main
            id="main-content"
            class="mx-auto w-full max-w-3xl min-w-0 px-5 py-7"
        >
            <slot />
        </main>
        <nav
            aria-label="Main navigation"
            class="fixed inset-x-0 bottom-0 z-30 border-t pb-[env(safe-area-inset-bottom)]"
            :class="
                performance
                    ? 'border-white/10 bg-[#101916]'
                    : 'border-black/10 bg-[#f6f7f2]'
            "
        >
            <div class="mx-auto grid max-w-3xl grid-cols-2 gap-2 px-5 py-2">
                <Link
                    :href="index()"
                    :aria-current="!performance ? 'page' : undefined"
                    class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-xs font-semibold"
                    :class="
                        !performance
                            ? 'bg-[#e6ede3] text-[#284f40]'
                            : 'text-stone-400 hover:bg-white/5'
                    "
                >
                    <Library class="size-5" /> Songs
                </Link>
                <Link
                    :href="songPerformance({ slug: songSlug, draftId })"
                    :aria-current="performance ? 'page' : undefined"
                    class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-xs font-semibold"
                    :class="
                        performance
                            ? 'bg-lime-200/10 text-lime-200'
                            : 'text-stone-500 hover:bg-black/5'
                    "
                >
                    <AudioLines class="size-5" /> Performance
                </Link>
            </div>
        </nav>
    </div>
</template>
