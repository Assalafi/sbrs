<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1/remita-split-report')
    ->middleware(['throttle:60,1', 'remita.split.api'])
    ->group(function () {
        Route::get('summary', [\App\Http\Controllers\Api\RemitaSplitReportController::class, 'summary']);
    });
