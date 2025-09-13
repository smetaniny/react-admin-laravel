<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Smetaniny\ReactAdminCore\Services\ResourceManager;
use Smetaniny\ReactAdminLaravel\Controllers\ReactAdminController;

/**
 * Service Provider для React Admin Laravel
 */
class ReactAdminLaravelServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     */
    public function register(): void
    {
        // Регистрируем ResourceManager как синглтон
        $this->app->singleton(
            ResourceManager::class, function ($app) {
                return new ResourceManager();
            }
        );

        // Мержим конфигурацию
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/react-admin.php',
            'react-admin'
        );
    }

    /**
     * Загрузка сервисов
     */
    public function boot(): void
    {
        // Публикация конфигурации
        $this->publishes(
            [
            __DIR__ . '/../../config/react-admin.php' => config_path('react-admin.php'),
            ], 'react-admin-config'
        );

        // Публикация миграций
        $this->publishes(
            [
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
            ], 'react-admin-migrations'
        );

        // Загрузка миграций
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Регистрация маршрутов
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Регистрация команд
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                // Здесь будут команды Artisan
                ]
            );
        }

        // Автоматическая регистрация ресурсов из конфигурации
        $this->registerResourcesFromConfig();
    }

    /**
     * Регистрация ресурсов из конфигурации
     */
    private function registerResourcesFromConfig(): void
    {
        $resourceManager = $this->app->make(ResourceManager::class);
        $resources = config('react-admin.resources', []);

        foreach ($resources as $name => $config) {
            if (!isset($config['class'])) {
                continue;
            }

            // Создаем экземпляр ресурса
            $resourceInstance = $this->app->make($config['class']);
            $resourceManager->registerResource($name, $resourceInstance);

            // Регистрируем политику, если указана
            if (isset($config['policy'])) {
                $policyInstance = $this->app->make($config['policy']);
                $resourceManager->registerPolicy($name, $policyInstance);
            }

            // Устанавливаем конфигурацию
            if (isset($config['config'])) {
                $resourceManager->setResourceConfig($name, $config['config']);
            }
        }
    }

    /**
     * Получить сервисы, предоставляемые провайдером
     */
    public function provides(): array
    {
        return [
            ResourceManager::class,
        ];
    }
}
