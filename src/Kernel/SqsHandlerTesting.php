<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Kernel;

use Bref\Context\Context;
use Bref\Event\Sqs\SqsRecord;
use CustomerGauge\Bref\Queue\LambdaJob;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\Queue;

final class SqsHandlerTesting
{
    private $container;

    public function __construct(Container  $container)
    {
        $this->container = $container;
    }

    public function dispatchSqsJob(Job $job)
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

        $sqs->run(new LambdaJob($this->container, $sqsMessage), Context::fake());
    }
}