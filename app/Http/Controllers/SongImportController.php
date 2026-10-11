<?php

namespace App\Http\Controllers;

use App\Actions\ParseSongChart;
use App\Http\Requests\ImportSongRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class SongImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('songs/Import');
    }

    public function preview(ImportSongRequest $request, ParseSongChart $parser): JsonResponse
    {
        $chart = $parser->parse($request->string('source')->toString());
        $original = $request->string('original_source')->toString() ?: $request->string('source')->toString();
        $originalChart = $parser->parse($original);
        $chart['warnings'] = array_values(array_unique([...$originalChart['warnings'], ...$chart['warnings']]));
        $token = Crypt::encryptString(json_encode([
            'user' => $request->user()->id,
            'source' => hash('sha256', $chart['canonical']),
            'original' => hash('sha256', $original),
        ], JSON_THROW_ON_ERROR));

        return response()->json([...$chart, 'reviewToken' => $token, 'originalSource' => $original]);
    }
}
