import assert from 'node:assert/strict';
import { test } from 'node:test';
import { readChartFile } from '../../resources/js/lib/importFile.ts';

await test('reads UTF-8 text and ChordPro without losing spacing or lyrics', async () => {
    for (const extension of [
        'txt',
        'cho',
        'chopro',
        'chordpro',
        'crd',
        'pro',
    ]) {
        const source = '  G    D\r\n  Été encore\r\n';
        assert.equal(
            await readChartFile(new File([source], `song.${extension}`)),
            source,
        );
    }
});

await test('rejects unsupported files, binary content, empty text, invalid UTF-8, and oversized charts', async () => {
    for (const file of [
        new File(['text'], 'song.pdf'),
        new File(['a\0b'], 'song.txt'),
        new File(['  '], 'song.txt'),
        new File([new Uint8Array([0xff, 0xfe])], 'song.txt'),
        new File(['x'.repeat(100001)], 'song.txt'),
    ]) {
        await assert.rejects(readChartFile(file));
    }
});
