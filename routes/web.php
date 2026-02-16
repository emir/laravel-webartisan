<?php

use Emir\Webartisan\Middleware\WebartisanEnabled;
use Emir\Webartisan\WebartisanController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('webartisan.route_prefix', 'webartisan'),
    'domain' => config('webartisan.domain'),
    'middleware' => array_merge(
        config('webartisan.middleware', ['web']),
        [WebartisanEnabled::class]
    ),
    'as' => 'webartisan.',
], function () {
    Route::get('/', [WebartisanController::class, 'index'])->name('index');
    Route::post('/run', [WebartisanController::class, 'run'])->name('run');
    Route::get('/commands', [WebartisanController::class, 'commands'])->name('commands');
});
