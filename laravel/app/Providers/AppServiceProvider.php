<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SqliteBackupService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Espelhamento automático de modelos no banco SQLite de backup
        $mirrorModels = [
            Order::class,
            OrderItem::class,
            Product::class,
            Category::class,
            User::class,
            SystemSetting::class,
        ];

        foreach ($mirrorModels as $modelClass) {
            $modelClass::saved(function ($model) {
                try {
                    SqliteBackupService::mirrorRecord($model->getTable(), $model->getAttributes());
                } catch (\Throwable $e) {
                    // Silencioso para não interromper a operação principal
                }
            });

            $modelClass::deleted(function ($model) {
                try {
                    SqliteBackupService::deleteRecord($model->getTable(), $model->getKey());
                } catch (\Throwable $e) {
                    // Silencioso para não interromper a operação principal
                }
            });
        }
    }
}
