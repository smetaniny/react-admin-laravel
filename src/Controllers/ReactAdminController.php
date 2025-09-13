<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Smetaniny\ReactAdminCore\Contracts\ResourceInterface;
use Smetaniny\ReactAdminCore\Exceptions\ReactAdminCoreException;
use Smetaniny\ReactAdminCore\Services\ResourceManager;
use Smetaniny\ReactAdminLaravel\Http\LaravelRequest;
use Smetaniny\ReactAdminLaravel\Http\LaravelResponse;

/**
 * Laravel контроллер для React Admin
 */
class ReactAdminController extends Controller
{
    public function __construct(
        private ResourceManager $resourceManager
    ) {
    }

    /**
     * Получить список ресурсов
     */
    public function index(Request $request, string $resource): JsonResponse
    {
        try {
            $resourceInstance = $this->getResource($resource, $request);
            
            $filters = $request->get('filter', []);
            $sort = $this->parseSortParameter($request->get('sort', []));
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 10);

            $result = $resourceInstance->getList($filters, $sort, $page, $perPage);

            return response()->json(
                [
                'success' => true,
                'data' => $result['data'] ?? $result,
                'pagination' => $result['pagination'] ?? null,
                ]
            );

        } catch (ReactAdminCoreException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Получить один ресурс
     */
    public function show(Request $request, string $resource, int|string $id): JsonResponse
    {
        try {
            $resourceInstance = $this->getResource($resource, $request);
            $result = $resourceInstance->getOne($id);

            return response()->json(
                [
                'success' => true,
                'data' => $result,
                ]
            );

        } catch (ReactAdminCoreException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Создать новый ресурс
     */
    public function store(Request $request, string $resource): JsonResponse
    {
        try {
            $resourceInstance = $this->getResource($resource, $request);
            $result = $resourceInstance->create($request->all());

            return response()->json(
                [
                'success' => true,
                'data' => $result,
                'message' => 'Resource created successfully',
                ], 201
            );

        } catch (ReactAdminCoreException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Обновить ресурс
     */
    public function update(Request $request, string $resource, int|string $id): JsonResponse
    {
        try {
            $resourceInstance = $this->getResource($resource, $request);
            $result = $resourceInstance->update($id, $request->all());

            return response()->json(
                [
                'success' => true,
                'data' => $result,
                'message' => 'Resource updated successfully',
                ]
            );

        } catch (ReactAdminCoreException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Удалить ресурс
     */
    public function destroy(Request $request, string $resource, int|string $id): JsonResponse
    {
        try {
            $resourceInstance = $this->getResource($resource, $request);
            $resourceInstance->delete($id);

            return response()->json(
                [
                'success' => true,
                'message' => 'Resource deleted successfully',
                ]
            );

        } catch (ReactAdminCoreException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Получить метаданные ресурса
     */
    public function metadata(Request $request, string $resource): JsonResponse
    {
        try {
            if (!$this->resourceManager->hasResource($resource)) {
                return response()->json(
                    [
                    'success' => false,
                    'message' => "Resource '{$resource}' not found",
                    ], 404
                );
            }

            $config = $this->resourceManager->getResourceConfig($resource);
            $resourceInstance = $this->resourceManager->getResource($resource);

            $metadata = [
                'name' => $resource,
                'allowed_filters' => method_exists($resourceInstance, 'getAllowedFilters') 
                    ? $resourceInstance->getAllowedFilters() 
                    : [],
                'allowed_sorts' => method_exists($resourceInstance, 'getAllowedSorts') 
                    ? $resourceInstance->getAllowedSorts() 
                    : [],
                'config' => $config,
            ];

            return response()->json(
                [
                'success' => true,
                'data' => $metadata,
                ]
            );

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Получить экземпляр ресурса
     */
    private function getResource(string $resourceName, Request $request): ResourceInterface
    {
        $resource = $this->resourceManager->getResource($resourceName);
        
        // Устанавливаем текущего пользователя
        if ($request->user()) {
            $resource->setUser($request->user());
        }

        return $resource;
    }

    /**
     * Парсить параметр сортировки
     */
    private function parseSortParameter($sort): array
    {
        if (is_string($sort)) {
            // Формат: "field,direction" или "field"
            $parts = explode(',', $sort);
            $field = $parts[0];
            $direction = $parts[1] ?? 'asc';
            return [$field => $direction];
        }

        if (is_array($sort)) {
            return $sort;
        }

        return [];
    }

    /**
     * Обработать исключение
     */
    private function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof ReactAdminCoreException) {
            return response()->json(
                [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => method_exists($e, 'getErrors') ? $e->getErrors() : null,
                ], $e->getStatusCode()
            );
        }

        // Логируем неожиданные ошибки
        logger()->error(
            'React Admin Controller Error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            ]
        );

        return response()->json(
            [
            'success' => false,
            'message' => 'Internal server error',
            ], 500
        );
    }
}
