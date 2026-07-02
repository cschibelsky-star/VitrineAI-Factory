<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:status', function () {
    $this->info('Aplicação gerada pela Vitrine AI Factory.');
});
