<?php

use App\Http\Controllers\Api\GoogleAdsWebhookController;
use App\Http\Controllers\Api\LeadCaptureController;
use App\Http\Controllers\Api\MetaWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/leads/capture', [LeadCaptureController::class, 'store'])->name('api.leads.capture');

Route::prefix('webhooks')->group(function () {
    Route::match(['get', 'post'], '/whatsapp/{tenant}', function (\Illuminate\Http\Request $request, string $tenant) {
        $controller = app(WhatsAppWebhookController::class);
        if ($request->isMethod('get')) {
            return $controller->verify($request, $tenant);
        }

        return $controller->handle($request, $tenant);
    });

    Route::match(['get', 'post'], '/meta/{tenant}', function (\Illuminate\Http\Request $request, string $tenant) {
        $controller = app(MetaWebhookController::class);
        if ($request->isMethod('get')) {
            return $controller->verify($request, $tenant);
        }

        return $controller->handle($request, $tenant);
    });

    Route::post('/google/{tenant}', [GoogleAdsWebhookController::class, 'handle']);
});
