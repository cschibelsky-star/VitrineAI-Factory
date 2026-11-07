<?php

use App\Http\Controllers\ViaFactoryMissionController;
use Illuminate\Support\Facades\Route;

Route::get('/via/factory/missions/{mission}', [ViaFactoryMissionController::class, 'show'])
    ->whereNumber('mission');

Route::post('/via/factory/missions/{mission}/steps/{step}/correction',
    [\App\Http\Controllers\ViaFactoryCorrectionController::class, 'store'])
    ->whereNumber('mission')->whereNumber('step')->middleware('throttle:10,1');
