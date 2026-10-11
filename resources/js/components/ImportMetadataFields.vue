<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ImportMetadata } from '@/types/import';
const metadata = defineModel<ImportMetadata>({ required: true });
defineProps<{ errors: Partial<Record<keyof ImportMetadata, string>> }>();
const optionalFields = [
    {
        key: 'key',
        label: 'Key',
        placeholder: 'G, Am, B♭…',
        type: 'text',
        maxLength: 30,
    },
    {
        key: 'bpm',
        label: 'BPM',
        placeholder: 'Not known',
        type: 'number',
        min: 20,
        max: 400,
    },
    {
        key: 'capo',
        label: 'Capo (fret)',
        placeholder: 'Not set',
        type: 'number',
        min: 0,
        max: 24,
    },
    {
        key: 'meter',
        label: 'Time signature',
        placeholder: '4/4',
        type: 'text',
        maxLength: 10,
    },
] as const;
</script>

<template>
    <fieldset class="space-y-4">
        <legend class="mb-4 text-xl font-semibold">Song details</legend>
        <div class="space-y-2">
            <Label for="import-title"
                >Title <span aria-hidden="true">*</span></Label
            ><Input
                id="import-title"
                v-model="metadata.title"
                required
                maxlength="255"
                class="h-12 bg-white text-base md:text-base"
            /><InputError :message="errors.title" />
        </div>
        <div class="space-y-2">
            <Label for="import-artist">Artist</Label
            ><Input
                id="import-artist"
                v-model="metadata.artist"
                maxlength="255"
                class="h-12 bg-white text-base md:text-base"
            /><InputError :message="errors.artist" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div
                v-for="field in optionalFields"
                :key="field.key"
                class="min-w-0 space-y-2"
            >
                <Label :for="`import-${field.key}`">{{ field.label }}</Label
                ><Input
                    :id="`import-${field.key}`"
                    v-model="metadata[field.key]"
                    :type="field.type"
                    :placeholder="field.placeholder"
                    :min="'min' in field ? field.min : undefined"
                    :max="'max' in field ? field.max : undefined"
                    :maxlength="
                        'maxLength' in field ? field.maxLength : undefined
                    "
                    class="h-12 bg-white text-base md:text-base"
                /><InputError :message="errors[field.key]" />
            </div>
        </div>
        <div class="space-y-2">
            <Label for="import-source-url">Source link (optional)</Label
            ><Input
                id="import-source-url"
                v-model="metadata.source_url"
                type="url"
                maxlength="2048"
                placeholder="https://…"
                class="h-12 bg-white text-base md:text-base"
            /><InputError :message="errors.source_url" />
        </div>
        <p class="text-sm text-stone-500">
            Leave unknown details blank. Capo is recorded as a note; chords are
            not transposed.
        </p>
    </fieldset>
</template>
