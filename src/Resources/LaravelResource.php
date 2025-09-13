<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Smetaniny\ReactAdminCore\Abstracts\AbstractResource;
use Smetaniny\ReactAdminCore\Contracts\PolicyInterface;
use Smetaniny\ReactAdminCore\Exceptions\ResourceNotFoundException;
use Smetaniny\ReactAdminCore\Exceptions\ValidationException;

/**
 * Laravel реализация ресурса с поддержкой Eloquent
 */
abstract class LaravelResource extends AbstractResource
{
    protected string $model;
    protected array $validationRules = [];

    public function __construct(?PolicyInterface $policy = null)
    {
        parent::__construct($policy);
        
        if (!isset($this->model)) {
            throw new \InvalidArgumentException('Property $model must be defined in ' . static::class);
        }
    }

    /**
     * Установить правила валидации
     */
    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Найти ресурс по ID
     */
    protected function findById(int|string $id)
    {
        $model = $this->getQuery()->find($id);
        
        if (!$model) {
            throw new ResourceNotFoundException("Resource with ID {$id} not found");
        }

        return $model;
    }

    /**
     * Найти все ресурсы с фильтрацией, сортировкой и пагинацией
     */
    protected function findAll(array $filters, array $sort, int $page, int $perPage): array
    {
        $query = $this->getQuery();

        // Применяем фильтры
        $query = $this->applyFiltersToQuery($query, $filters);

        // Применяем сортировку
        $query = $this->applySortingToQuery($query, $sort);

        // Пагинация
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->formatPaginatedResponse($paginator);
    }

    /**
     * Сохранить новый ресурс
     */
    protected function store(array $data)
    {
        $modelClass = $this->model;
        $model = new $modelClass();
        $model->fill($data);
        $model->save();

        return $model;
    }

    /**
     * Обновить ресурс по ID
     */
    protected function updateById(int|string $id, array $data)
    {
        $model = $this->findById($id);
        $model->fill($data);
        $model->save();

        return $model;
    }

    /**
     * Удалить ресурс по ID
     */
    protected function deleteById(int|string $id): bool
    {
        $model = $this->findById($id);
        return $model->delete();
    }

    /**
     * Валидировать данные
     */
    protected function validateData(array $data, string $context = 'create'): array
    {
        if (empty($this->validationRules)) {
            return $data;
        }

        $rules = $this->getValidationRules($context);
        
        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        return $validator->validated();
    }

    /**
     * Получить правила валидации для контекста
     */
    protected function getValidationRules(string $context): array
    {
        if (isset($this->validationRules[$context])) {
            return $this->validationRules[$context];
        }

        return $this->validationRules['default'] ?? $this->validationRules;
    }

    /**
     * Получить базовый запрос
     */
    protected function getQuery(): Builder
    {
        $modelClass = $this->model;
        return $modelClass::query();
    }

    /**
     * Применить фильтры к запросу
     */
    protected function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Поддержка различных типов фильтров
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } elseif (str_contains($value, '%')) {
                $query->where($field, 'like', $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Применить сортировку к запросу
     */
    protected function applySortingToQuery(Builder $query, array $sort): Builder
    {
        foreach ($sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }

    /**
     * Форматировать ответ с пагинацией
     */
    protected function formatPaginatedResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Преобразовать ресурс для вывода
     */
    protected function transformResource($resource): array
    {
        if ($resource instanceof Model) {
            return $resource->toArray();
        }

        return parent::transformResource($resource);
    }

    /**
     * Настроить ресурс
     */
    public function configure(array $config): self
    {
        if (isset($config['validation_rules'])) {
            $this->setValidationRules($config['validation_rules']);
        }

        return $this;
    }

    /**
     * Получить поля ресурса (должно быть реализовано в наследниках)
     */
    abstract public function fields(): array;

    /**
     * Получить правила валидации для создания (опционально)
     */
    public function createRules(): array
    {
        return [];
    }

    /**
     * Получить правила валидации для обновления (опционально)
     */
    public function updateRules(): array
    {
        return [];
    }

    /**
     * Получить фильтры ресурса (опционально)
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Получить действия ресурса (опционально)
     */
    public function actions(): array
    {
        return [
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
        ];
    }
}
