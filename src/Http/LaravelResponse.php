<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\Http;

use Illuminate\Http\JsonResponse;
use Smetaniny\ReactAdminCore\Contracts\ResponseInterface;

/**
 * Laravel реализация интерфейса ответа
 */
class LaravelResponse implements ResponseInterface
{
    private int $statusCode = 200;
    private array $data = [];
    private ?string $message = null;
    private array $headers = [];

    /**
     * Установить HTTP статус код
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Получить HTTP статус код
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Установить данные ответа
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Получить данные ответа
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Установить сообщение
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Получить сообщение
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Установить заголовки
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Добавить заголовок
     */
    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Получить заголовки
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Преобразовать в массив
     */
    public function toArray(): array
    {
        $response = [
            'success' => $this->statusCode >= 200 && $this->statusCode < 300,
            'data' => $this->data,
        ];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        return $response;
    }

    /**
     * Преобразовать в JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Создать Laravel JsonResponse
     */
    public function toJsonResponse(): JsonResponse
    {
        return response()->json($this->toArray(), $this->statusCode, $this->headers);
    }
}
