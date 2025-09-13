<?php

return [
    /*
    |--------------------------------------------------------------------------
    | React Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Конфигурация для React Admin Laravel пакета
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для API маршрутов
    |
    */
    'routes' => [
        'prefix' => 'api/admin',
        'middleware' => ['api', 'auth:sanctum'],
        'name' => 'react-admin.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources Configuration
    |--------------------------------------------------------------------------
    |
    | Конфигурация ресурсов для автоматической регистрации
    |
    */
    'resources' => [
        // Пример конфигурации ресурса пользователей
        // 'users' => [
        //     'class' => \App\ReactAdmin\Resources\UserResource::class,
        //     'policy' => \App\ReactAdmin\Policies\UserPolicy::class,
        //     'config' => [
        //         'allowed_filters' => ['name', 'email', 'role'],
        //         'allowed_sorts' => ['name', 'email', 'created_at'],
        //         'validation_rules' => [
        //             'create' => [
        //                 'name' => 'required|string|max:255',
        //                 'email' => 'required|email|unique:users',
        //                 'password' => 'required|string|min:8',
        //             ],
        //             'update' => [
        //                 'name' => 'sometimes|string|max:255',
        //                 'email' => 'sometimes|email|unique:users,email',
        //                 'password' => 'sometimes|string|min:8',
        //             ],
        //         ],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default User Model
    |--------------------------------------------------------------------------
    |
    | Модель пользователя по умолчанию
    |
    */
    'user_model' => \Smetaniny\ReactAdminLaravel\Models\LaravelUser::class,

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки аутентификации
    |
    */
    'auth' => [
        'guard' => 'web',
        'provider' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки пагинации по умолчанию
    |
    */
    'pagination' => [
        'default_per_page' => 10,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки кеширования
    |
    */
    'cache' => [
        'enabled' => env('REACT_ADMIN_CACHE_ENABLED', false),
        'ttl' => env('REACT_ADMIN_CACHE_TTL', 3600),
        'prefix' => 'react_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки валидации по умолчанию
    |
    */
    'validation' => [
        'stop_on_first_failure' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки обработки ошибок
    |
    */
    'errors' => [
        'log_exceptions' => true,
        'include_trace' => env('APP_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки CORS для API
    |
    */
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['*'],
    ],
];
