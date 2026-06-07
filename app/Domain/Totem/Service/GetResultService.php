<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use App\Domain\Totem\Exception\ClientNotFoundException;
use App\Domain\Totem\Exception\ResultNotFoundException;
use Illuminate\Database\Capsule\Manager as Capsule;

final class GetResultService
{
    /**
     * @return array{public_id:string,result:array{animal_code:string,animal_name:string,result_title:string,result_description:string,result_image_path:string}}
     */
    public function getByPublicId(string $publicId): array
    {
        $client = $this->findClientByPublicId($publicId);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        $this->updateClientLastSeen((int) $client['id']);

        $result = $this->findLatestResultByPublicId($publicId);

        if ($result === null) {
            throw new ResultNotFoundException();
        }

        return $this->buildResponse($result);
    }

    /**
     * @return array{id:int,public_id:string}|null
     */
    public function findClientByPublicId(string $publicId): ?array
    {
        $client = Capsule::table('app_clients')
            ->where('public_id', $publicId)
            ->first(['id', 'public_id']);

        if ($client === null) {
            return null;
        }

        return [
            'id' => (int) $client->id,
            'public_id' => (string) $client->public_id,
        ];
    }

    public function updateClientLastSeen(int $clientId): void
    {
        $timestamp = date('Y-m-d H:i:s');

        Capsule::table('app_clients')
            ->where('id', $clientId)
            ->update([
                'last_seen_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
    }

    /**
     * @return array{
     *     public_id:string,
     *     animal_code:string,
     *     animal_name:string,
     *     result_title:string,
     *     result_description:string,
     *     result_image_path:string
     * }|null
     */
    public function findLatestResultByPublicId(string $publicId): ?array
    {
        $result = Capsule::table('test_results')
            ->where('public_id', $publicId)
            ->orderByDesc('created_at')
            ->limit(1)
            ->first([
                'public_id',
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
            'public_id' => (string) $result->public_id,
            'animal_code' => (string) $result->animal_code,
            'animal_name' => (string) $result->animal_name,
            'result_title' => (string) $result->result_title,
            'result_description' => (string) $result->result_description,
            'result_image_path' => (string) $result->result_image_path,
        ];
    }

    /**
     * @param array{public_id:string,animal_code:string,animal_name:string,result_title:string,result_description:string,result_image_path:string} $result
     * @return array{public_id:string,result:array{animal_code:string,animal_name:string,result_title:string,result_description:string,result_image_path:string}}
     */
    public function buildResponse(array $result): array
    {
        return [
            'public_id' => $result['public_id'],
            'result' => [
                'animal_code' => $result['animal_code'],
                'animal_name' => $result['animal_name'],
                'result_title' => $result['result_title'],
                'result_description' => $result['result_description'],
                'result_image_path' => $result['result_image_path'],
            ],
        ];
    }
}
