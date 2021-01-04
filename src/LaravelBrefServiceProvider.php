<?php declare(strict_types=1);

namespace CustomerGauge\Bref;

use Aws\CodePipeline\CodePipelineClient;
use Bref\Context\Context;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class LaravelBrefServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerAwsCodepipelineClient();
    }

    private function registerAwsCodepipelineClient()
    {
        $this->app->bind('AWS_REGION', function () {
            $region = config('aws.region');

            if (! $region) {
                throw new RuntimeException('Region was not configured');
            }

            return $region;
        });

        $this->app->bind(CodePipelineClient::class, function () {
            return new CodePipelineClient([
                'version' => '2015-07-09',
                'region' => $this->app->get('AWS_REGION'),
            ]);
        });

        $this->app->bind(Context::class, function () {
            if (isset($_SERVER['LAMBDA_INVOCATION_CONTEXT'])) {
                $lambdaContext = json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'], true);
            } else {
                $lambdaContext = [];
            }

            return new Context(
                $lambdaContext['awsRequestId'] ?? '',
                $lambdaContext['deadlineMs'] ?? '',
                $lambdaContext['invokedFunctionArn'] ?? '',
                $lambdaContext['traceId'] ?? '',
            );
        });
    }
}
