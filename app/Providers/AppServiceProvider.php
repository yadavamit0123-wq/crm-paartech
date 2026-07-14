<?php

namespace App\Providers;

use App\Models\Lead;
use App\Observers\LeadObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard(false);
        Schema::defaultStringLength(191);
        Lead::observe(LeadObserver::class);

        foreach ([
            storage_path('app/livewire-tmp'),
            storage_path('app/public/documents/logos'),
            storage_path('app/public/documents/items'),
            storage_path('app/public/documents/attachments'),
        ] as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }
    }
}
