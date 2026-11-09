<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('marketplace:about', function () {
    $this->info(config('app.name').' foundation');
    $this->line('Locales: fa, ku, en');
    $this->line('Status: foundation scaffold');
})->purpose('Display marketplace foundation information');
