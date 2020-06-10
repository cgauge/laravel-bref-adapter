<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Helpers;

use Aws\CodePipeline\CodePipelineClient;

final class CodepipelineAction
{
    private $client;

    public function __construct(CodePipelineClient $client)
    {
        $this->client = $client;
    }

    public function notify($job, $status)
    {
        if ($status == 0) {
            $this->client->putJobSuccessResult([
                'jobId' => $job,
            ]);
        } else {
            $this->client->putJobFailureResult([
                'jobId' => $job,
                'failureDetails' => [
                    'message' => 'Lambda execution failed',
                    'type' => 'JobFailed',
                ]
            ]);
        }
    }
}
