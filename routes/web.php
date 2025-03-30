<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Pages\Main::class)->name('main');
Route::get('/settings', \App\Livewire\Pages\Settings::class)->name('settings');
Route::get('/tools/set-analyzer', \App\Livewire\Pages\Tools\SetAnalyzer::class)->name('tools.set-analyzer');
Route::get('/inventory', \App\Livewire\Pages\Inventory::class)->name('inventory');
Route::get('/storages', \App\Livewire\Pages\Storages::class)->name('storages');
