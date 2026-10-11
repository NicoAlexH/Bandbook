export const musicianRoles = [
    'Singer',
    'Guitarist',
    'Bassist',
    'Pianist',
    'Percussionist',
] as const;

export type MusicianRole = (typeof musicianRoles)[number];

export type SongSection = {
    id: string;
    name: string;
    bars: number | null;
    energy: string;
    lines: MusicLine[];
    instructions: Record<MusicianRole, string>;
};

export type MusicLine = {
    chord: string;
    lyric: string;
    segments?: { chord: string; lyric: string }[];
};

export type DemoSong = {
    slug: string;
    title: string;
    artist: string;
    key: string;
    bpm: number | null;
    capo?: number | null;
    draftId?: number;
    sourceUrl?: string | null;
    originalSource?: string;
    reviewedSource?: string;
    meter: string;
    mood: string;
    duration: string;
    note: string;
    sections: SongSection[];
};
