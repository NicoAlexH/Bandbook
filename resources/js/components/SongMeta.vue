<script setup lang="ts">
import type { DemoSong } from '@/types/music';
defineProps<{ song: DemoSong }>();
</script>

<template>
    <dl class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <div class="flex items-baseline gap-1.5">
            <dt class="opacity-60">Key</dt>
            <dd class="font-semibold">{{ song.key }}</dd>
        </div>
        <div class="flex items-baseline gap-1.5">
            <dt class="sr-only">Tempo</dt>
            <dd>
                <span class="font-semibold">{{ song.bpm ?? 'Not set' }}</span>
                <span class="opacity-60">{{
                    song.bpm === null ? 'tempo' : 'BPM'
                }}</span>
            </dd>
        </div>
        <div>
            <dt class="sr-only">Time signature</dt>
            <dd class="opacity-60">{{ song.meter }}</dd>
        </div>
        <div
            v-if="song.capo !== null && song.capo !== undefined"
            class="flex items-baseline gap-1.5"
        >
            <dt class="opacity-60">Capo</dt>
            <dd class="font-semibold">{{ song.capo }}</dd>
        </div>
    </dl>
</template>
