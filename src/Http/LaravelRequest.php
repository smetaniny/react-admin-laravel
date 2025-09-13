<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\Http;

use Illuminate\Http\Request;
use Smetaniny\ReactAdminCore\Contracts\RequestInterface;
use Smetaniny\ReactAdminCore\Contracts\UserInterface;

/**
 * Laravel реализация интерфейса запроса
 */
class LaravelRequest implements RequestInterface
{
    public function __construct(
        private Request $request
    ) {
    }

    /**
     * Получить HTTP метод запроса
     */
    public function getMethod(): string
    {
        return $this->request->method();
    }

    /**
     * Получить путь запроса
     */
    public function getPath(): string
    {
        return $this->request->path();
    }

    /**
     * Получить параметры запроса (query parameters)
     */
    public function getParameters(): array
    {
        return $this->request->query();
    }

    /**
     * Получить тело запроса
     */
    public function getBody(): array
    {
        return $this->request->all();
    }

    /**
     * Получить заголовки запроса
     */
    public function getHeaders(): array
    {
        return $this->request->headers->all();
    }

    /**
     * Получить аутентифицированного пользователя
     */
    public function getUser(): ?UserInterface
    {
        $user = $this->request->user();
        
        if ($user && $user instanceof UserInterface) {
            return $user;
        }

        return null;
    }

    /**
     * Получить конкретный параметр
     */
    public function getParameter(string $key, $default = null)
    {
        return $this->request->query($key, $default);
    }

    /**
     * Получить конкретное поле из тела запроса
     */
    public function getBodyField(string $key, $default = null)
    {
        return $this->request->input($key, $default);
    }

    /**
     * Получить конкретный заголовок
     */
    public function getHeader(string $key, $default = null): ?string
    {
        return $this->request->header($key, $default);
    }

    /**
     * Получить оригинальный Laravel Request
     */
    public function getOriginalRequest(): Request
    {
        return $this->request;
    }
}
