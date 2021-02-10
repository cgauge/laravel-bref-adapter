<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Kernel;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Event\Sqs\SqsEvent;
use CustomerGauge\Bref\Queue\LambdaJob;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
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
        $input = new SqsEvent($event);

        $job = new LambdaJob($this->container, $input->getRecords()[0]);

        $this->container->instance(Context::class, $context);

        $this->container->instance(LambdaJob::class, $job);

        $this->dispatcher()->dispatch(new JobProcessing('lambda', $job));

        try {
            $job->fire();

            $this->dispatcher()->dispatch(new JobProcessed('lambda', $job));
        } catch (Throwable $e) {
            $this->exception->report($e);

            $this->dispatcher()->dispatch(new JobExceptionOccurred('lambda', $job, $e));

            throw $e;
        }
    }

    private function dispatcher(): Dispatcher
    {
        return $this->dispatcher ??= $this->container->make(Dispatcher::class);
    }
}
