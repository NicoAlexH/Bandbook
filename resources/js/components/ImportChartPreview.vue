<script setup lang="ts">
import { computed } from 'vue';
import ChordLine from '@/components/ChordLine.vue';
import type { ChartPreview } from '@/types/import';
const props = defineProps<{ preview: ChartPreview }>();
const contents = computed(
    () =>
        new Map(props.preview.contents.map((section) => [section.id, section])),
);
</script>

<template>
    <section aria-labelledby="import-preview-title" class="space-y-4">
        <h2 id="import-preview-title" class="text-xl font-semibold">
            Music preview
            <span class="text-sm font-normal text-stone-500"
                >· {{ preview.occurrences.length }} sections</span
            >
        </h2>
        <ol class="space-y-4">
            <li
                v-for="(occurrence, index) in preview.occurrences"
                :key="occurrence.id"
                class="min-w-0 rounded-xl border border-black/10 bg-white p-4"
            >
                <h3 class="mb-4 font-semibold [overflow-wrap:anywhere]">
                    <span class="mr-2 font-mono text-xs text-stone-500">{{
                        index + 1
                    }}</span
                    >{{ occurrence.name }}
                </h3>
                <div class="space-y-4">
                    <ChordLine
                        v-for="(line, lineIndex) in contents.get(
                            occurrence.sectionId,
                        )?.lines"
                        :key="lineIndex"
                        :line="line"
                    />
                </div>
            </li>
        </ol>
    </section>
</template>
