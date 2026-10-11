<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { SongSection } from '@/types/music';
defineProps<{ sections: SongSection[]; activeIndex: number }>();
const emit = defineEmits<{ navigate: [index: number] }>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <Button
                variant="outline"
                class="h-13 border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white"
                :disabled="activeIndex === 0"
                @click="emit('navigate', activeIndex - 1)"
                ><ChevronLeft /> Previous</Button
            >
            <Button
                class="h-13 bg-lime-200 text-emerald-950 hover:bg-lime-100"
                :disabled="activeIndex === sections.length - 1"
                @click="emit('navigate', activeIndex + 1)"
                >Next section <ChevronRight
            /></Button>
        </div>
        <nav
            aria-label="Song sections"
            class="grid grid-cols-2 gap-2 sm:grid-cols-3"
        >
            <button
                v-for="(section, index) in sections"
                :key="section.id"
                type="button"
                :aria-current="index === activeIndex ? 'step' : undefined"
                class="min-h-12 min-w-0 rounded-xl border px-3 py-2 text-left text-sm [overflow-wrap:anywhere] transition-colors focus-visible:outline-2 focus-visible:outline-lime-200"
                :class="
                    index === activeIndex
                        ? 'border-lime-200/50 bg-lime-200/10 text-lime-200'
                        : 'border-white/10 text-stone-400 hover:bg-white/5'
                "
                @click="emit('navigate', index)"
            >
                <span class="mr-2 font-mono text-xs opacity-60">{{
                    String(index + 1).padStart(2, '0')
                }}</span
                >{{ section.name }}
            </button>
        </nav>
    </div>
</template>
