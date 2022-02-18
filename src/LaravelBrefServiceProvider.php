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

        $this->brefContext();
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
    }

    private function brefContext(): void
    {
        // When using Bref Function instead of Bref FPM, there will be no SERVER header
        // because we already are inside the runtime execution. That means the callable
        // function has received the Context and should be able to bind it directly.
        // see https://github.com/cgauge/laravel-bref-adapter/blob/dfafb6f204d8d725f427e47906549cad18f66c69/handlers/request.php#L11
        if ($this->app->has(Context::class)) {
            return;
        }

        $this->app->bind(Context::class, function () {
            // When using Bref with FPM Layer, Bref will json_encode the Execution Context
            // and inject it as a SERVER header on FPM so that we (users) can retrieve
            // and use it. That means we can decode and reconstruct the original
            // Bref Context object.
            if (isset($_SERVER['LAMBDA_INVOCATION_CONTEXT'])) {
                $lambdaContext = json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'], true);
            } else {
                $lambdaContext = [];
            }

            return new Context(
                $lambdaContext['awsRequestId'] ?? '',
                $lambdaContext['deadlineMs'] ?? 0,
                $lambdaContext['invokedFunctionArn'] ?? '',
                $lambdaContext['traceId'] ?? '',
            );
        });
    }
}
