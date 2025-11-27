<?php

namespace App\Infrastructure\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ElasticsearchClient
{
    private Client $client;

    private string $index;

    public function __construct()
    {
        $config = config('search.elasticsearch');

        $host = sprintf(
            '%s://%s:%s',
            $config['scheme'] ?? 'http',
            $config['host'] ?? 'localhost',
            $config['port'] ?? 9200,
        );

        $builder = ClientBuilder::create()->setHosts([$host]);

        if (! empty($config['user']) && ! empty($config['password'])) {
            $builder->setBasicAuthentication($config['user'], $config['password']);
        }

        if (! empty($config['ca_bundle'])) {
            $builder->setSSLVerification($config['ca_bundle']);
        }

        $this->client = $builder
            ->setLogger(Log::getLogger())
            ->build();

        $this->index = $config['index'] ?? 'itens-normalizados';
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @param  array<mixed>  $body
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function search(array $body, array $options = []): array
    {
        $params = array_merge(
            [
                'index' => $this->index,
                'body' => $body,
            ],
            $options,
        );

        return $this->client->search($params)->asArray();
    }

    public function ping(): bool
    {
        try {
            $response = $this->client->ping();

            if (method_exists($response, 'asBool')) {
                return (bool) $response->asBool();
            }

            return (bool) Arr::get((array) $response, 'acknowledged', true);
        } catch (\Throwable $throwable) {
            Log::warning('Elasticsearch ping failed', [
                'message' => $throwable->getMessage(),
            ]);

            return false;
        }
    }
}
