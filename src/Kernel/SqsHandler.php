<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Kernel;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Event\Sqs\SqsEvent;
use CustomerGauge\Bref\Queue\LambdaJob;
use Exception;
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

    /** @var ExceptionHandler */
    private $exception;

    /** @var Dispatcher */
    private $dispatcher;

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
            // Here we're wrapping whatever exception we get into a base exception because Laravel ignores some exceptions
            // by using internalDontReport on the ExceptionHandler class. However, we're a background process here and
            // it is better to report everything for visibility. We can't disable the internalDontReport without
            // installing the whole `laravel/framework` because it's an `\Illuminate\Foundation` class.
            $exception = new Exception(
                '[laravel-bref-adapter-error] [' . get_class($e) . '] ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );

            $this->exception->report($exception);

            $this->dispatcher()->dispatch(new JobExceptionOccurred('lambda', $job, $e));

            throw $e;
        }
    }

    private function dispatcher(): Dispatcher
    {
        if ($this->dispatcher) {
            return $this->dispatcher;
        }

        $this->dispatcher = $this->container->make(Dispatcher::class);

        return $this->dispatcher;
    }
}
