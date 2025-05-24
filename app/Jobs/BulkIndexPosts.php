<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkIndexPosts implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected int $batchSize = 1000;

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function handle(): void {
        $client = ClientBuilder::create()
            ->setHosts(config('services.elasticsearch.hosts'))
            ->setRetries(2)
            ->setHttpClientOptions([
                'timeout' => 120,
                'connect_timeout' => 60,
            ])
            ->build();

        Post::query()->where(function ($query) {
            $query
                ->whereNull('last_indexed_at')
                ->orWhereColumn('updated_at', '>', 'last_indexed_at');
        })
            ->orderBy('id')
            ->chunk($this->batchSize, function ($posts) use ($client) {
                $params = ['body' => []];

                /**@var Post $post */
                foreach ($posts as $post) {
                    $params['body'][] = [
                        'index' => [
                            '_index' => 'posts',
                            '_id' => $post->id,
                        ],
                    ];
                    $params['body'][] = $post->toSearchableArray();
                }

                if (!empty($params['body'])) {
                    $client->bulk($params);
                }

                $posts->each(function ($post) {
                    $post->update(['last_indexed_at' => now()]);
                });

                logger()?->info("Indexed {$posts->count()} posts}");
            });
    }
}
