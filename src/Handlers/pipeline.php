<?php

/*
|--------------------------------------------------------------------------
| Pipeline Handler
|--------------------------------------------------------------------------
|
| This handler is prepared to notify back an AWS CodePipeline about
| the execution status. If we don't, AWS CodePipeline execution
| will always be marked as failed.
*/

$app = require __DIR__ .'/boot.php';

return function (array $event) use ($app) {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    $app->singleton(\CustomerGauge\Bref\Input\LambdaInput::class, function () use ($event) {
        return new \CustomerGauge\Bref\Input\LambdaInput($event);
    });

    $status = $kernel->handle(
        $input = new \CustomerGauge\Bref\Input\ArtisanLambdaInput,
        new \Symfony\Component\Console\Output\ConsoleOutput
    );

    $jobId = $event['CodePipeline.job']['id'];

    $pipeline = $app->make(\CustomerGauge\Bref\Helpers\CodepipelineAction::class);

    $pipeline->notify($jobId, $status);

    $kernel->terminate($input, $status);
};
