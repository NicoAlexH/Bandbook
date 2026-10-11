<?php

use App\Http\Controllers\SongDraftController;
use App\Http\Controllers\SongImportController;
use App\Http\Controllers\SongPrototypeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('songs', [SongPrototypeController::class, 'index'])->name('songs.index');
Route::middleware('auth')->group(function () {
    Route::get('songs/import', [SongImportController::class, 'create'])->name('songs.import');
    Route::post('songs/import/preview', [SongImportController::class, 'preview'])->middleware('throttle:30,1')->name('songs.preview');
    Route::post('song-drafts', [SongDraftController::class, 'store'])->middleware('throttle:30,1')->name('song-drafts.store');
    Route::get('song-drafts/{songDraft}', [SongDraftController::class, 'show'])->name('song-drafts.show');
    Route::get('song-drafts/{songDraft}/perform', [SongDraftController::class, 'perform'])->name('song-drafts.perform');
});
Route::get('songs/{song}', [SongPrototypeController::class, 'show'])->name('songs.show');
Route::get('songs/{song}/perform', [SongPrototypeController::class, 'perform'])->name('songs.perform');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
