<?php

/*
|--------------------------------------------------------------------------
| Queue Handler
|--------------------------------------------------------------------------
|
| This Handler is prepared to handle invocations from SQS. When dispatching
| a job from Laravel, the class gets serialized and put into SQS. We need
| to deserialize and fire the job. If any exception happens, we should
| let it bubble up to Bref so that the Lambda is marked as failed.
| The Laravel Console Kernel catches all exceptions and does not
| rethrow, making it impossible to flag the Lambda as failed.
| This Handler will bootstrap Laravel using the Console
| Kernel without silencing the exceptions.
*/

$app = require __DIR__ . '/boot.php';

$handler = $app->make(\CustomerGauge\Bref\Kernel\SqsHandler::class);

$handler->bootstrap();

return $handler;
