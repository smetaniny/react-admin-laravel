# React Admin Laravel

Laravel-специфичная реализация для React Admin. Этот пакет предоставляет готовые к использованию контроллеры, модели и сервисы для быстрого создания административных панелей с Laravel и React Admin.

## 🚀 Особенности

- **Готовые контроллеры** - CRUD API из коробки
- **Eloquent интеграция** - полная поддержка Laravel ORM
- **Автоматическая валидация** - встроенная валидация данных
- **Система прав доступа** - интеграция с Laravel Gate и Policy
- **Конфигурируемые маршруты** - гибкая настройка API endpoints
- **Middleware поддержка** - интеграция с Laravel middleware

## 📦 Установка

```bash
composer require smetaniny/react-admin-laravel
```

### Публикация конфигурации

```bash
php artisan vendor:publish --tag=react-admin-config
```

### Публикация миграций

```bash
php artisan vendor:publish --tag=react-admin-migrations
php artisan migrate
```

## 🔧 Настройка

### 1. Конфигурация

Отредактируйте файл `config/react-admin.php`:

```php
return [
    'routes' => [
        'prefix' => 'api/admin',
        'middleware' => ['api', 'auth:sanctum'],
        'name' => 'react-admin.',
    ],

    'resources' => [
        'users' => [
            'class' => \App\ReactAdmin\Resources\UserResource::class,
            'policy' => \App\ReactAdmin\Policies\UserPolicy::class,
            'config' => [
                'allowed_filters' => ['name', 'email', 'role'],
                'allowed_sorts' => ['name', 'email', 'created_at'],
                'validation_rules' => [
                    'create' => [
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|string|min:8',
                    ],
                    'update' => [
                        'name' => 'sometimes|string|max:255',
                        'email' => 'sometimes|email|unique:users,email',
                        'password' => 'sometimes|string|min:8',
                    ],
                ],
            ],
        ],
    ],
];
```

### 2. Создание ресурса

```php
<?php

namespace App\ReactAdmin\Resources;

use App\Models\User;
use Smetaniny\ReactAdminLaravel\Resources\LaravelResource;

class UserResource extends LaravelResource
{
    protected array $allowedFilters = ['name', 'email', 'role'];
    protected array $allowedSorts = ['name', 'email', 'created_at'];

    public function __construct()
    {
        parent::__construct(User::class);

        $this->setValidationRules([
            'create' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
            ],
            'update' => [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email',
                'password' => 'sometimes|string|min:8',
            ],
        ]);
    }

    protected function transformResource($resource): array
    {
        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'email' => $resource->email,
            'roles' => $resource->roles ?? [],
            'created_at' => $resource->created_at?->toISOString(),
            'updated_at' => $resource->updated_at?->toISOString(),
        ];
    }
}
```

### 3. Создание политики

```php
<?php

namespace App\ReactAdmin\Policies;

use App\Models\User;
use Smetaniny\ReactAdminCore\Abstracts\AbstractPolicy;
use Smetaniny\ReactAdminCore\Contracts\UserInterface;

class UserPolicy extends AbstractPolicy
{
    public function canViewAny(UserInterface $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function canView(UserInterface $user, $resource = null): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->isOwner($user, $resource);
    }

    public function canCreate(UserInterface $user): bool
    {
        return $user->hasRole('admin');
    }

    public function canUpdate(UserInterface $user, $resource = null): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->isOwner($user, $resource);
    }

    public function canDelete(UserInterface $user, $resource = null): bool
    {
        return $user->hasRole('admin') && !$this->isOwner($user, $resource);
    }
}
```

### 4. Обновление модели пользователя

```php
<?php

namespace App\Models;

use Smetaniny\ReactAdminLaravel\Models\LaravelUser;

class User extends LaravelUser
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'permissions',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'roles' => 'array',
        'permissions' => 'array',
    ];
}
```

## 🔗 API Endpoints

После настройки будут доступны следующие endpoints:

```
GET    /api/admin/{resource}              - Список ресурсов
GET    /api/admin/{resource}/{id}         - Получить ресурс
POST   /api/admin/{resource}              - Создать ресурс
PUT    /api/admin/{resource}/{id}         - Обновить ресурс
DELETE /api/admin/{resource}/{id}         - Удалить ресурс
GET    /api/admin/{resource}/metadata     - Метаданные ресурса
```

### Примеры запросов

#### Получить список пользователей с фильтрацией

```bash
GET /api/admin/users?filter[role]=admin&sort=name,asc&page=1&per_page=10
```

#### Создать нового пользователя

```bash
POST /api/admin/users
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "roles": ["editor"]
}
```

## 🔒 Аутентификация и авторизация

### Настройка Sanctum

```php
// config/sanctum.php
'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
```

### Middleware для API

```php
// В config/react-admin.php
'routes' => [
    'middleware' => ['api', 'auth:sanctum', 'verified'],
],
```

## 🧪 Тестирование

```bash
# Запуск тестов
composer test

# Тесты с покрытием
composer test-coverage

# Статический анализ
composer phpstan

# Проверка стиля кода
composer cs-check
```

## 📚 Расширенное использование

### Кастомные фильтры

```php
class UserResource extends LaravelResource
{
    protected function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        $query = parent::applyFiltersToQuery($query, $filters);

        // Кастомный фильтр по дате регистрации
        if (isset($filters['registered_after'])) {
            $query->where('created_at', '>=', $filters['registered_after']);
        }

        // Поиск по имени и email
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }
}
```

### Кастомная валидация

```php
class UserResource extends LaravelResource
{
    protected function validateData(array $data, string $context = 'create'): array
    {
        $rules = $this->getValidationRules($context);

        // Добавляем кастомные правила
        if ($context === 'update' && isset($data['email'])) {
            $rules['email'] .= ',email,' . $data['id'];
        }

        $validator = validator($data, $rules);

        // Кастомная валидация
        $validator->after(function ($validator) use ($data) {
            if (isset($data['roles']) && in_array('admin', $data['roles'])) {
                if (!auth()->user()->hasRole('super_admin')) {
                    $validator->errors()->add('roles', 'Only super admin can assign admin role');
                }
            }
        });

        if ($validator->fails()) {
            throw new ValidationException(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        return $validator->validated();
    }
}
```

## 🤝 Вклад в проект

1. Форкните репозиторий
2. Создайте ветку для новой функции (`git checkout -b feature/amazing-feature`)
3. Зафиксируйте изменения (`git commit -m 'Add some amazing feature'`)
4. Отправьте в ветку (`git push origin feature/amazing-feature`)
5. Откройте Pull Request

## 📄 Лицензия

Этот проект лицензирован под MIT License - см. файл [LICENSE](LICENSE) для деталей.

## 🔗 Связанные пакеты

- `smetaniny/react-admin-core` - Универсальное ядро
- `smetaniny/react-admin-routing` - Основной пакет-фасад

## 📞 Поддержка

- Email: sm.sergey.v@yandex.ru
- Website: https://smetaniny.ru/
- GitHub Issues: https://github.com/smetaniny/react-admin-laravel/issues
