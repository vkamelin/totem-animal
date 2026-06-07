<?php

declare(strict_types=1);

namespace App\Middleware;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class VkSignatureMiddleware implements MiddlewareInterface
{
    private string $clientSecret;

    public function __construct(string $clientSecret)
    {
        if ($clientSecret === '') {
            throw new InvalidArgumentException('VK client secret must not be empty.');
        }

        $this->clientSecret = $clientSecret;
    }

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $this->extractLaunchParams($request);

        if ($params === [] || !$this->isValidSignature($params)) {
            return $this->unauthorizedResponse();
        }

        $request = $request->withAttribute('vk_launch_params', $params);

        return $handler->handle($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractLaunchParams(ServerRequestInterface $request): array
    {
        $source = trim((string) $request->getHeaderLine('X-VK-Launch-Params'));

        if ($source === '') {
            $source = $request->getUri()->getQuery();
        }

        if ($source === '') {
            return [];
        }

        parse_str($source, $params);

        if (!is_array($params)) {
            return [];
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function isValidSignature(array $params): bool
    {
        if (!array_key_exists('sign', $params) || !is_string($params['sign']) || $params['sign'] === '') {
            return false;
        }

        $receivedSign = $params['sign'];
        unset($params['sign']);

        $signedParams = [];

        foreach ($params as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'vk_')) {
                continue;
            }

            $signedParams[$key] = $value;
        }

        ksort($signedParams);

        $canonicalQuery = http_build_query($signedParams);
        $calculatedSign = $this->base64UrlEncode(
            hash_hmac('sha256', $canonicalQuery, $this->clientSecret, true)
        );

        return hash_equals($receivedSign, $calculatedSign);
    }

    private function unauthorizedResponse(): ResponseInterface
    {
        $response = new Response(401);

        $response->getBody()->write(
            (string) json_encode(
                [
                    'success' => false,
                    'error' => 'invalid_vk_signature',
                ],
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            )
        );

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    private function base64UrlEncode(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }
}
