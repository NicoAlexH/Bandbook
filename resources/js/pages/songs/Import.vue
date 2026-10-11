<script setup lang="ts">
import { Head, Link, useForm, useHttp } from '@inertiajs/vue3';
import { ArrowLeft, Upload, Eye, Save } from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import ImportChartPreview from '@/components/ImportChartPreview.vue';
import ImportMetadataFields from '@/components/ImportMetadataFields.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import BandbookLayout from '@/layouts/BandbookLayout.vue';
import { readChartFile } from '@/lib/importFile';
import { index, preview as previewRoute } from '@/routes/songs';
import { store } from '@/routes/song-drafts';
import type { ChartPreview, ImportMetadata } from '@/types/import';

const source = ref('');
const original = ref('');
const fileName = ref('');
const fileError = ref('');
const requestError = ref('');
const readingFile = ref(false);
const preview = ref<ChartPreview | null>(null);
const metadata = ref<ImportMetadata>({
    title: '',
    artist: '',
    key: '',
    bpm: '',
    capo: '',
    meter: '',
    source_url: '',
});
const http = useHttp<{ source: string; original_source: string }, ChartPreview>(
    { source: '', original_source: '' },
);
const form = useForm({
    ...metadata.value,
    source: '',
    original_source: '',
    review_token: '',
    confirmed: false,
});
const busy = computed(
    () => http.processing || form.processing || readingFile.value,
);
const previewIsCurrent = computed(
    () => preview.value !== null && source.value === preview.value.canonical,
);
const chartError = computed(
    () =>
        http.errors.source ||
        form.errors.source ||
        fileError.value ||
        requestError.value,
);

watch(source, () => {
    form.confirmed = false;
});

async function loadFile(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    readingFile.value = true;
    fileError.value = '';
    try {
        const text = await readChartFile(file);
        source.value = text;
        original.value = text;
        fileName.value = file.name;
        preview.value = null;
        metadata.value = {
            title: '',
            artist: '',
            key: '',
            bpm: '',
            capo: '',
            meter: '',
            source_url: '',
        };
        http.clearErrors();
        form.clearErrors();
    } catch (error) {
        fileError.value =
            error instanceof Error
                ? error.message
                : 'Could not read this file.';
    } finally {
        readingFile.value = false;
        input.value = '';
    }
}

async function previewChart() {
    fileError.value = '';
    requestError.value = '';
    form.clearErrors();
    http.source = source.value;
    http.original_source = original.value || source.value;
    try {
        const result = await http.post(previewRoute.url());
        preview.value = result;
        source.value = result.canonical;
        original.value = result.originalSource;
        for (const key of [
            'title',
            'artist',
            'key',
            'bpm',
            'capo',
            'meter',
        ] as const) {
            if (!metadata.value[key])
                metadata.value[key] = result.metadata[key] ?? '';
        }
        form.confirmed = false;
        await nextTick();
        document
            .getElementById('review-chart')
            ?.scrollIntoView({ block: 'start' });
    } catch {
        if (!http.hasErrors)
            requestError.value =
                'Preview could not be loaded. Check your connection and sign-in, then try again. Your text is still here.';
    }
}

function saveDraft() {
    if (!previewIsCurrent.value || !preview.value || busy.value) return;
    form.transform((data) => ({
        ...data,
        ...metadata.value,
        source: source.value,
        original_source: original.value,
        review_token: preview.value!.reviewToken,
    })).post(store.url());
}
</script>

<template>
    <BandbookLayout>
        <Head title="Import a song · Bandbook" />
        <div class="space-y-7">
            <Link
                :href="index()"
                class="inline-flex min-h-11 items-center gap-2 text-sm text-stone-500"
                ><ArrowLeft class="size-4" /> All songs</Link
            >
            <header>
                <p
                    class="mb-2 text-xs font-semibold tracking-widest text-[#64816e] uppercase"
                >
                    Make it your own
                </p>
                <h1 class="text-3xl font-semibold tracking-tight">
                    Import a song
                </h1>
                <p class="mt-3 text-sm leading-relaxed text-stone-500">
                    Paste lyrics with chords, or choose a text / ChordPro file.
                    Review the music, then save a private draft to your account.
                </p>
            </header>
            <section class="space-y-4" aria-label="Import source">
                <Label
                    for="chart-file"
                    class="flex min-h-14 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-emerald-800/30 bg-white p-4 text-sm focus-within:ring-2 focus-within:ring-emerald-600"
                    ><Upload class="size-5" />
                    {{
                        readingFile
                            ? 'Reading file…'
                            : 'Choose a text or ChordPro file'
                    }}<input
                        id="chart-file"
                        type="file"
                        class="sr-only"
                        accept=".txt,.cho,.chopro,.chordpro,.crd,.pro"
                        :disabled="busy"
                        @change="loadFile"
                /></Label>
                <p class="text-xs [overflow-wrap:anywhere] text-stone-500">
                    {{
                        fileName ||
                        'UTF-8 text · up to 100 KB · one song per import'
                    }}
                </p>
                <div class="space-y-2">
                    <Label for="chart-source">{{
                        preview ? 'Edit the chart' : 'Or paste your chord sheet'
                    }}</Label
                    ><textarea
                        id="chart-source"
                        v-model="source"
                        :disabled="busy"
                        rows="12"
                        spellcheck="false"
                        aria-describedby="chart-help chart-error"
                        class="w-full min-w-0 resize-y rounded-xl border border-black/15 bg-white p-4 font-mono text-base leading-relaxed [overflow-wrap:anywhere] whitespace-pre-wrap focus-visible:outline-2 focus-visible:outline-emerald-700 disabled:opacity-60"
                        placeholder="[Verse 1]&#10;G                    Em&#10;Walking through the morning&#10;&#10;[Chorus]&#10;[C]Leave a little [G]light for me"
                    />
                </div>
                <p
                    id="chart-help"
                    class="text-sm leading-relaxed text-stone-500"
                >
                    Use [Verse 1] or [Chorus] for sections and [G]word for chord
                    placement. After preview, the chart uses this editable
                    format. Check chord alignment before saving.
                </p>
                <p
                    v-if="chartError"
                    id="chart-error"
                    role="alert"
                    class="text-sm text-red-700"
                >
                    {{ chartError }}
                </p>
                <Button
                    type="button"
                    :disabled="busy || !source.trim()"
                    class="h-12 w-full rounded-xl bg-[#284f40] text-white hover:bg-[#35634f]"
                    @click="previewChart"
                    ><Eye class="size-4" />
                    {{
                        http.processing
                            ? 'Reading your chart…'
                            : preview
                              ? 'Update preview'
                              : 'Preview song'
                    }}</Button
                >
            </section>
            <form
                v-if="preview"
                id="review-chart"
                class="scroll-mt-5 space-y-7"
                @submit.prevent="saveDraft"
            >
                <div
                    v-if="!previewIsCurrent"
                    role="status"
                    class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"
                >
                    The chart has changed. Update the preview before saving.
                </div>
                <ImportMetadataFields
                    v-model="metadata"
                    :errors="form.errors"
                />
                <div
                    v-if="preview.warnings.length"
                    class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"
                >
                    <h2 class="mb-2 font-semibold">Check these details</h2>
                    <ul class="list-disc space-y-2 pl-4">
                        <li
                            v-for="warning in preview.warnings"
                            :key="warning"
                            class="[overflow-wrap:anywhere]"
                        >
                            {{ warning }}
                        </li>
                    </ul>
                </div>
                <ImportChartPreview :preview="preview" />
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-4 text-sm leading-relaxed"
                    ><input
                        v-model="form.confirmed"
                        type="checkbox"
                        class="mt-1 size-5 shrink-0 accent-emerald-800"
                        :disabled="!previewIsCurrent || busy"
                    />
                    I have reviewed the sections, chord placement and any
                    warnings.</label
                >
                <InputError
                    :message="
                        form.errors.confirmed ||
                        form.errors.review_token ||
                        form.errors.original_source
                    "
                />
                <Button
                    type="submit"
                    :disabled="!previewIsCurrent || !form.confirmed || busy"
                    class="h-14 w-full rounded-xl bg-[#284f40] text-base text-white hover:bg-[#35634f]"
                    ><Save class="size-5" />
                    {{
                        form.processing ? 'Saving draft…' : 'Save private draft'
                    }}</Button
                >
                <p class="text-center text-xs leading-relaxed text-stone-500">
                    Only you can see this draft. It is not shared or approved
                    for performance. The original text is retained with your
                    import.
                </p>
            </form>
        </div>
    </BandbookLayout>
</template>
