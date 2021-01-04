<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Kernel;

use CustomerGauge\Bref\Queue\LambdaJob;
use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Event\Sqs\SqsEvent;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Console\Kernel;
use Throwable;

final class SqsHandler implements Handler
{
    private $kernel;

    private $container;

    /** @var ExceptionHandler  */
    private $exception;

    public function __construct(Kernel $kernel, Container $container)
    {
        $this->kernel = $kernel;
        $this->container = $container;
        $this->exception = $container->get(ExceptionHandler::class);
    }

    public function bootstrap(): void
    {
        $this->kernel->bootstrap();
    }

    public function handle($event, Context $context): void
    {
        $this->container->instance(Context::class, $context);

        $input = new SqsEvent($event);

        $job = new LambdaJob($this->container, $input->getRecords()[0]);

        try {
            $job->fire();
        } catch (Throwable $e) {
            $this->exception->report($e);

            throw $e;
        }
    }
}
