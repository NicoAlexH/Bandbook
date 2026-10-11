<script setup lang="ts">
import { musicianRoles } from '@/types/music';
import type { MusicianRole } from '@/types/music';
const role = defineModel<MusicianRole>({ required: true });
</script>

<template>
    <div class="flex min-w-0 items-center gap-3">
        <label for="musician-role" class="shrink-0 text-sm text-stone-400"
            >Your part</label
        >
        <select
            id="musician-role"
            v-model="role"
            class="min-h-12 min-w-0 flex-1 rounded-xl border border-white/20 bg-[#1b2923] px-3 text-base font-semibold text-white outline-none focus-visible:ring-2 focus-visible:ring-lime-200"
        >
            <option
                v-for="option in musicianRoles"
                :key="option"
                :value="option"
            >
                {{ option }}
            </option>
        </select>
    </div>
</template>
