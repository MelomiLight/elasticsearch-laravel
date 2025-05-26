<?php

namespace App\Traits;

use App\Jobs\IndexModelToElasticsearch;
use Elastic\Elasticsearch\ClientBuilder;
use App\Jobs\RemoveModelFromElasticsearch;

trait ESearchable
{
    public static function bootESearchable(): void {
        static::saved(static function ($model) {
            dispatch(new IndexModelToElasticsearch($model));
        });

        static::deleted(static function ($model) {
            dispatch(new RemoveModelFromElasticsearch($model));
        });
    }

    /**
     */
    public function getEsIndexName(): string {
        return property_exists($this, 'esIndex')
            ? $this->esIndex
            : $this->getTable();
    }

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    protected static function esClient() {
        static $client;
        if (!$client) {
            $client = ClientBuilder::create()
                ->setHosts(config('services.elasticsearch.hosts'))
                ->setRetries(2)
                ->setHttpClientOptions([
                    'timeout' => 120,
                    'connect_timeout' => 60,
                ])
                ->build();
        }
        return $client;
    }

    /**
     * @param string $q запрос
     * @param array $opt ['from'=>0,'size'=>10]
     * @return array
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public static function search(string $q, array $opt = []): array {
        $instance = new static;
        $index = $instance->getEsIndexName();

        $should = [];

        foreach (static::$esSearchableFields as $field => $weight) {
            $should[] = [
                'match' => [
                    $field => [
                        'query' => $q,
                        'boost' => $weight,
                    ],
                ],
            ];
        }

        foreach (static::$esSearchableRelations as $relation => $fields) {
            foreach ($fields as $f => $weight) {
                $should[] = [
                    'match' => [
                        "$relation.$f" => [
                            'query' => $q,
                            'boost' => $weight,
                        ],
                    ],
                ];
            }
        }

        $body = [
            'query' => [
                'bool' => [
                    'should' => $should,
                ],
            ],
        ];

        $params = array_merge([
            'index' => $index,
            'body' => $body,
        ], $opt);

        $resp = static::esClient()->search($params);

        return collect($resp['hits']['hits'])->pluck('_id')->all();
    }
}
