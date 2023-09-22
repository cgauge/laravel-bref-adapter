<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Kernel;

use Bref\Context\Context;
use Bref\Event\Sqs\SqsRecord;
use CustomerGauge\Bref\Queue\LambdaJob;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Queue;
use Throwable;

final class SqsHandlerTesting
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatchSqsJob($job, bool $suppressException = false)
    {
        $queue = new class extends Queue {
            public function createPayload($job, $queue, $data = '')
            {
                return parent::createPayload($job, $queue, $data);
            }
        };

        $payload = $queue->createPayload($job, 'phpunit');

        $sqsMessage = new SqsRecord([
            'eventSource' => 'aws:sqs',
            'body' => $payload,
        ]);

        /** @var SqsHandler $sqs */
        $sqs = $this->container->make(SqsHandler::class);

        try {
            $sqs->run(new LambdaJob($this->container, $sqsMessage), Context::fake());
        } catch (Throwable $t) {
            if (! $suppressException) {
                throw $t;
            }
        }
    }
}