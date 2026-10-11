export async function readChartFile(file: File): Promise<string> {
    if (!/\.(txt|cho|chopro|chordpro|crd|pro)$/i.test(file.name)) {
        throw new Error(
            'Choose a .txt, .cho, .chopro, .chordpro, .crd or .pro text file.',
        );
    }
    if (file.size > 100_000) {
        throw new Error('Choose a chart smaller than 100 KB.');
    }
    let source: string;
    try {
        source = new TextDecoder('utf-8', { fatal: true }).decode(
            await file.arrayBuffer(),
        );
    } catch {
        throw new Error(
            'This file could not be read as UTF-8 text. Export it as UTF-8 and try again.',
        );
    }
    if (
        Array.from(source).some(
            (character) =>
                character.charCodeAt(0) < 32 &&
                !['\t', '\n', '\r'].includes(character),
        )
    ) {
        throw new Error(
            'This looks like a binary file. Choose a plain-text chord sheet.',
        );
    }
    if (!source.trim()) {
        throw new Error(
            'This file is empty. Choose a chart containing lyrics or chords.',
        );
    }
    return source;
}
