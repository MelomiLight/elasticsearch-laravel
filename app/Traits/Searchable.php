<?php

declare(strict_types=1);

namespace App\Traits;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

trait Searchable
{
    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     */
    public function search(string $query, int $size = 10, int $from = 0): array {
        $params = [
            'index' => $this->getIndex(),
            'body' => [
                'from' => $from,
                'size' => $size,
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => $this->getSearchableFields(),
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ],
        ];

        $response = $this->client()->search($params);

        return collect($response['hits']['hits'])->pluck('_source')->toArray();
    }

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     */
    public function indexDocument(): void {
        $this->client()->index([
            'index' => $this->getIndex(),
            'id' => $this->getKey(),
            'body' => $this->toSearchableArray(),
        ]);
    }

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     */
    public function deleteDocument(): void {
        $this->client()->delete([
            'index' => $this->getIndex(),
            'id' => $this->getKey(),
        ]);
    }

    private function getIndex(): string {
        if (!empty($this->index)) {
            return $this->index;
        }

        return $this->getTable();
    }

    private function getSearchableFields(): array {
        if (method_exists($this, 'searchableFields')) {
            return $this->searchableFields();
        }

        return [];
    }

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    private function client(): Client {
        return ClientBuilder::create()
            ->setHosts(config('services.elasticsearch.hosts'))
            ->setRetries(2)
            ->setHttpClientOptions([
                'timeout' => 120,
                'connect_timeout' => 60,
            ])
            ->build();
    }
}
