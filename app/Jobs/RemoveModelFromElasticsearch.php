<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Elastic\Elasticsearch\Exception\ElasticsearchException;

class RemoveModelFromElasticsearch implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected string $morphClass;
    protected mixed $modelId;

    public function __construct(Model $model) {
        $this->morphClass = $model->getMorphClass();
        $this->modelId = $model->getKey();
    }

    /**
     * Обработчик очереди: удаляет документ из ES
     *
     * @throws ElasticsearchException
     */
    public function handle(): void {
        $model = app($this->morphClass);
        $client = call_user_func([$this->morphClass, 'esClient']);

        $params = [
            'index' => $model->getEsIndexName(),
            'id' => $this->modelId,
        ];

        $client->delete($params);
    }
}
