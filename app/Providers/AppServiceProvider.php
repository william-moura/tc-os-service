<?php

namespace App\Providers;

use App\Application\Services\MessagePublisherInterface;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Infrastructure\Messaging\Publishers\WorkOrderRabbitMQPublisher;
use App\Infrastructure\Messaging\RabbitMQService;
use App\Infrastructure\Repositories\WorkOrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkOrderRepositoryInterface::class, WorkOrderRepository::class);
        $this->app->bind(MessagePublisherInterface::class, WorkOrderRabbitMQPublisher::class);
        this->app->singleton(RabbitMQService::class, fn () => RabbitMQService::fromEnv());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
