<?php

namespace App\Providers;

use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use App\Infrastructure\Search\ElasticsearchClient;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ElasticsearchClient::class);
        $this->app->singleton(JurimetriaSearchService::class);
    }
}
