<?php

use App\Http\Controllers\ViaFactoryMissionController;
use Illuminate\Support\Facades\Route;

Route::get('/via/factory/missions/{mission}', [ViaFactoryMissionController::class, 'show'])
    ->whereNumber('mission');
