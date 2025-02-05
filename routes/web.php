<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Pages\Main::class)->name('main');
Route::get('/settings', \App\Livewire\Pages\Settings::class)->name('settings');
Route::get('/tools/set-analyzer', \App\Livewire\Pages\Tools\SetAnalyzer::class)->name('tools.set-analyzer');
