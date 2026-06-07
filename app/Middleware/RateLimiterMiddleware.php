<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Infrastructure\RateLimit\RateLimiter;
use App\Infrastructure\RateLimit\RateLimitResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Psr7\Response;

final class RateLimiterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RateLimiter $rateLimiter,
        private readonly array $config,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($this->config['enabled'] ?? true)) {
            return $handler->handle($request);
        }

        $rule = $this->resolveRule($request);
        $identityKey = $this->buildIdentityKey($request);
        $redisKey = sprintf(
            '%s:%s:%s',
            $this->config['key_prefix'] ?? 'rate_limit',
            $rule['key'],
            $identityKey
        );

        try {
            $result = $this->rateLimiter->hit($redisKey, $rule['limit'], $rule['window']);
        } catch (RuntimeException) {
            return $this->unavailableResponse();
        }

        if (!$result->allowed) {
            return $this->tooManyRequestsResponse($result);
        }

        $response = $handler->handle($request);

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        return $this->withRateLimitHeaders($response, $result);
    }

    /**
     * @return array{key:string,limit:int,window:int}
     */
    private function resolveRule(ServerRequestInterface $request): array
    {
        $routeKey = $this->buildRouteKey($request);
        $rules = is_array($this->config['rules'] ?? null) ? $this->config['rules'] : [];
        $defaultLimit = (int) ($this->config['default_limit'] ?? 60);
        $defaultWindow = (int) ($this->config['default_window'] ?? 60);

        if (isset($rules[$routeKey]) && is_array($rules[$routeKey])) {
            return [
                'key' => $routeKey,
                'limit' => (int) ($rules[$routeKey]['limit'] ?? $defaultLimit),
                'window' => (int) ($rules[$routeKey]['window'] ?? $defaultWindow),
            ];
        }

        return [
            'key' => $routeKey,
            'limit' => $defaultLimit,
            'window' => $defaultWindow,
        ];
    }

    private function buildRouteKey(ServerRequestInterface $request): string
    {
        $method = strtoupper($request->getMethod());
        $pattern = $this->extractRoutePattern($request);

        if ($pattern === '') {
            $pattern = $request->getUri()->getPath() ?: '/';
        }

        return $method . ':' . $pattern;
    }

    private function extractRoutePattern(ServerRequestInterface $request): string
    {
        $route = $request->getAttribute('__route__');

        if (is_object($route) && method_exists($route, 'getPattern')) {
            $pattern = (string) $route->getPattern();

            if ($pattern !== '') {
                return $pattern;
            }
        }

        if (is_object($route) && method_exists($route, 'getRoutePattern')) {
            $pattern = (string) $route->getRoutePattern();

            if ($pattern !== '') {
                return $pattern;
            }
        }

        if (is_string($route) && $route !== '') {
            return $route;
        }

        $routeAttribute = $request->getAttribute('route');

        if (is_object($routeAttribute) && method_exists($routeAttribute, 'getPattern')) {
            $pattern = (string) $routeAttribute->getPattern();

            if ($pattern !== '') {
                return $pattern;
            }
        }

        if (is_string($routeAttribute) && $routeAttribute !== '') {
            return $routeAttribute;
        }

        return '';
    }

    private function buildIdentityKey(ServerRequestInterface $request): string
    {
        $body = $request->getParsedBody();

        if (is_array($body)) {
            $publicId = $this->extractPublicId($body['public_id'] ?? null);

            if ($publicId !== null) {
                return 'public:' . $publicId;
            }
        }

        $routePublicId = $this->extractPublicId($this->extractRoutePublicId($request));

        if ($routePublicId !== null) {
            return 'public:' . $routePublicId;
        }

        $vkLaunchParams = $request->getAttribute('vk_launch_params');
        if (is_array($vkLaunchParams) && array_key_exists('vk_user_id', $vkLaunchParams)) {
            $vkUserId = $vkLaunchParams['vk_user_id'];

            if (is_scalar($vkUserId) && (string) $vkUserId !== '') {
                return 'vk:' . hash('sha256', (string) $vkUserId);
            }
        }

        $clientIp = $this->extractClientIp($request);
        $userAgent = trim((string) $request->getHeaderLine('User-Agent'));

        if ($clientIp === null || $userAgent === '') {
            return 'anonymous';
        }

        return 'fp:' . hash('sha256', $clientIp . '|' . $userAgent);
    }

    private function extractPublicId(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $candidate = trim($value);

        if ($candidate === '') {
            return null;
        }

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $candidate)) {
            return null;
        }

        return $candidate;
    }

    private function extractRoutePublicId(ServerRequestInterface $request): mixed
    {
        foreach (['__route__', 'route'] as $attributeName) {
            $route = $request->getAttribute($attributeName);

            if (!is_object($route)) {
                continue;
            }

            if (method_exists($route, 'getArguments')) {
                $routeArgs = $route->getArguments();

                if (is_array($routeArgs) && array_key_exists('public_id', $routeArgs)) {
                    return $routeArgs['public_id'];
                }
            }

            if (method_exists($route, 'getRouteArguments')) {
                $routeArgs = $route->getRouteArguments();

                if (is_array($routeArgs) && array_key_exists('public_id', $routeArgs)) {
                    return $routeArgs['public_id'];
                }
            }
        }

        $publicIdAttribute = $request->getAttribute('public_id');

        return is_string($publicIdAttribute) ? $publicIdAttribute : null;
    }

    private function extractClientIp(ServerRequestInterface $request): ?string
    {
        $forwardedFor = trim((string) $request->getHeaderLine('X-Forwarded-For'));

        if ($forwardedFor !== '') {
            $firstIp = trim((string) explode(',', $forwardedFor, 2)[0]);

            if ($firstIp !== '') {
                return $firstIp;
            }
        }

        $realIp = trim((string) $request->getHeaderLine('X-Real-IP'));

        if ($realIp !== '') {
            return $realIp;
        }

        $serverParams = $request->getServerParams();
        $remoteAddr = trim((string) ($serverParams['REMOTE_ADDR'] ?? ''));

        return $remoteAddr !== '' ? $remoteAddr : null;
    }

    private function tooManyRequestsResponse(RateLimitResult $result): ResponseInterface
    {
        $response = $this->jsonResponse(
            429,
            [
                'success' => false,
                'error' => 'rate_limit_exceeded',
                'retry_after' => $result->retryAfter,
            ]
        );

        return $this->withRateLimitHeaders($response, $result)
            ->withHeader('Retry-After', (string) $result->retryAfter);
    }

    private function unavailableResponse(): ResponseInterface
    {
        return $this->jsonResponse(
            503,
            [
                'success' => false,
                'error' => 'rate_limiter_unavailable',
            ]
        );
    }

    private function withRateLimitHeaders(ResponseInterface $response, RateLimitResult $result): ResponseInterface
    {
        return $response
            ->withHeader('X-RateLimit-Limit', (string) $result->limit)
            ->withHeader('X-RateLimit-Remaining', (string) $result->remaining)
            ->withHeader('X-RateLimit-Reset', (string) $result->resetAfter);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(int $status, array $data): ResponseInterface
    {
        $response = new Response($status);
        $response->getBody()->write(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
