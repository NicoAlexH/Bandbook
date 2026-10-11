import { show, perform } from '@/routes/songs';
import {
    show as showDraft,
    perform as performDraft,
} from '@/routes/song-drafts';
import type { DemoSong } from '@/types/music';

export function songDetail(song: Pick<DemoSong, 'slug' | 'draftId'>) {
    return song.draftId ? showDraft(song.draftId) : show(song.slug);
}

export function songPerformance(song: Pick<DemoSong, 'slug' | 'draftId'>) {
    return song.draftId ? performDraft(song.draftId) : perform(song.slug);
}
