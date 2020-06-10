<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Queue;

use Bref\Event\Sqs\SqsRecord;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class LambdaJob extends Job implements JobContract
{
    private $job;

    public function __construct(Container $container, SqsRecord $job)
    {
        $this->job = $job;
        $this->container = $container;
        $this->connectionName = 'lambda';
    }

    public function getJobId()
    {
        $this->job->getMessageId();
    }

    public function getRawBody()
    {
        return $this->job->getBody();
    }

    public function attempts()
    {
        return $this->job->getApproximateReceiveCount();
    }
}