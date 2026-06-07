<?php

declare(strict_types=1);

namespace App\Domain\Client\Service;

use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\QueryException;
use RuntimeException;

final class ClientMeService
{
    private const int UUID_RETRY_LIMIT = 5;

    /**
     * @return array{public_id:string, client_id:int}
     */
    public function resolveClient(?string $publicId): array
    {
        return Capsule::connection()->transaction(function () use ($publicId): array {
            $now = $this->now();

            if ($publicId === null) {
                return $this->createClient($now);
            }

            $client = Capsule::table('app_clients')
                ->where('public_id', $publicId)
                ->first();

            if ($client !== null) {
                Capsule::table('app_clients')
                    ->where('id', $client->id)
                    ->update([
                        'last_seen_at' => $this->formatDateTime($now),
                        'updated_at' => $this->formatDateTime($now),
                    ]);

                return [
                    'public_id' => (string) $client->public_id,
                    'client_id' => (int) $client->id,
                ];
            }

            return $this->createClient($now);
        });
    }

    /**
     * @return array{
     *     animal_code:string,
     *     animal_name:string,
     *     result_title:string,
     *     result_description:string,
     *     result_image_path:string
     * }|null
     */
    public function findResult(string $publicId): ?array
    {
        $result = Capsule::table('test_results')
            ->where('public_id', $publicId)
            ->orderByDesc('created_at')
            ->limit(1)
            ->first([
                'animal_code',
                'animal_name',
                'result_title',
                'result_description',
                'result_image_path',
            ]);

        if ($result === null) {
            return null;
        }

        return [
            'animal_code' => (string) $result->animal_code,
            'animal_name' => (string) $result->animal_name,
            'result_title' => (string) $result->result_title,
            'result_description' => (string) $result->result_description,
            'result_image_path' => (string) $result->result_image_path,
        ];
    }

    /**
     * @param array{
     *     animal_code:string,
     *     animal_name:string,
     *     result_title:string,
     *     result_description:string,
     *     result_image_path:string
     * }|null $result
     * @return array{success:bool,data:array{public_id:string,result:array{animal_code:?string,animal_name:?string,result_title:?string,result_description:?string,result_image_path:?string}}}
     */
    public function buildResponse(string $publicId, ?array $result): array
    {
        return [
            'success' => true,
            'data' => [
                'public_id' => $publicId,
                'result' => [
                    'animal_code' => $result['animal_code'] ?? null,
                    'animal_name' => $result['animal_name'] ?? null,
                    'result_title' => $result['result_title'] ?? null,
                    'result_description' => $result['result_description'] ?? null,
                    'result_image_path' => $result['result_image_path'] ?? null,
                ],
            ],
        ];
    }

    /**
     * @return array{public_id:string, client_id:int}
     */
    private function createClient(DateTimeImmutable $now): array
    {
        $nowValue = $this->formatDateTime($now);

        for ($attempt = 0; $attempt < self::UUID_RETRY_LIMIT; $attempt++) {
            $publicId = $this->generateUuidV4();

            try {
                $clientId = (int) Capsule::table('app_clients')->insertGetId([
                    'public_id' => $publicId,
                    'first_seen_at' => $nowValue,
                    'last_seen_at' => $nowValue,
                    'created_at' => $nowValue,
                    'updated_at' => $nowValue,
                ]);

                return [
                    'public_id' => $publicId,
                    'client_id' => $clientId,
                ];
            } catch (QueryException $exception) {
                if (!$this->isDuplicateKeyException($exception) || $attempt === self::UUID_RETRY_LIMIT - 1) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Unable to create client.');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    private function isDuplicateKeyException(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo;

        if (!is_array($errorInfo) || !isset($errorInfo[0], $errorInfo[1])) {
            return false;
        }

        return (string) $errorInfo[0] === '23000' || (int) $errorInfo[1] === 1062;
    }
}
