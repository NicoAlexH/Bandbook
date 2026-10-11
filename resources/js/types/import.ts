import type { MusicLine } from './music';

export type ImportMetadata = {
    title: string;
    artist: string;
    key: string;
    bpm: string;
    capo: string;
    meter: string;
    source_url: string;
};

export type ChartPreview = {
    metadata: Record<string, string>;
    contents: { id: string; name: string; lines: MusicLine[] }[];
    occurrences: { id: string; sectionId: string; name: string }[];
    warnings: string[];
    canonical: string;
    reviewToken: string;
    originalSource: string;
};
