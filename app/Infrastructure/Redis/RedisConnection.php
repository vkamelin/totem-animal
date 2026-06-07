<?php

declare(strict_types=1);

namespace App\Infrastructure\Redis;

use InvalidArgumentException;
use Redis;
use RedisException;
use RuntimeException;

final class RedisConnection
{
    /**
     * @var array{host:string,port:int,password:?string,database:int,timeout:float,prefix:string}
     */
    private array $config;

    private ?Redis $client = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $host = isset($config['host']) ? trim((string) $config['host']) : '';
        $port = isset($config['port']) ? (int) $config['port'] : 0;
        $password = $config['password'] ?? null;
        $database = isset($config['database']) ? (int) $config['database'] : -1;
        $timeout = isset($config['timeout']) ? (float) $config['timeout'] : 0.0;
        $prefix = isset($config['prefix']) ? trim((string) $config['prefix']) : '';

        if ($host === '') {
            throw new InvalidArgumentException('Redis host must not be empty.');
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Redis port must be between 1 and 65535.');
        }

        if ($database < 0) {
            throw new InvalidArgumentException('Redis database must be greater than or equal to 0.');
        }

        if ($timeout <= 0.0) {
            throw new InvalidArgumentException('Redis timeout must be greater than 0.');
        }

        if ($password !== null && !is_string($password)) {
            throw new InvalidArgumentException('Redis password must be a string or null.');
        }

        $this->config = [
            'host' => $host,
            'port' => $port,
            'password' => $password === '' ? null : $password,
            'database' => $database,
            'timeout' => $timeout,
            'prefix' => $prefix,
        ];
    }

    public function getClient(): Redis
    {
        if ($this->client instanceof Redis) {
            return $this->client;
        }

        if (!class_exists(Redis::class)) {
            throw new RuntimeException('Redis extension is not installed.');
        }

        $client = new Redis();

        try {
            $connected = $client->connect(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } catch (RedisException $exception) {
            throw new RuntimeException('Unable to connect to Redis.', 0, $exception);
        }

        if ($connected !== true) {
            throw new RuntimeException('Unable to connect to Redis.');
        }

        $password = $this->config['password'];

        if (is_string($password) && $password !== '') {
            try {
                $authenticated = $client->auth($password);
            } catch (RedisException $exception) {
                throw new RuntimeException('Unable to authenticate with Redis.', 0, $exception);
            }

            if ($authenticated !== true) {
                throw new RuntimeException('Unable to authenticate with Redis.');
            }
        }

        if ($this->config['database'] > 0) {
            try {
                $selected = $client->select($this->config['database']);
            } catch (RedisException $exception) {
                throw new RuntimeException('Unable to select Redis database.', 0, $exception);
            }

            if ($selected !== true) {
                throw new RuntimeException('Unable to select Redis database.');
            }
        }

        if ($this->config['prefix'] !== '') {
            try {
                $optionSet = $client->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
            } catch (RedisException $exception) {
                throw new RuntimeException('Unable to configure Redis prefix.', 0, $exception);
            }

            if ($optionSet !== true) {
                throw new RuntimeException('Unable to configure Redis prefix.');
            }
        }

        $this->client = $client;

        return $this->client;
    }
}
