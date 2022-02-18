<?php

use Bref\Context\Context;
use Symfony\Component\HttpKernel\Exception\HttpException;

$app = require __DIR__ . '/boot.php';

return function (array $event, Context $context) use ($app) {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    $app->instance(Context::class, $context);

    if (! isset($event['LARAVEL_ROUTE'])) {
        throw new HttpException(404, 'The LARAVEL_ROUTE variable was not specified.');
    }

    if (! isset($event['LARAVEL_ROUTE_METHOD'])) {
        throw new HttpException(404, 'The LARAVEL_ROUTE_METHOD variable was not specified.');
    }

    $response = $kernel->handle(
        $request = \Illuminate\Http\Request::create(
            $event['LARAVEL_ROUTE'],
            $event['LARAVEL_ROUTE_METHOD'],
            $event['LARAVEL_REQUEST_BODY'] ?? []
        )
    );

    $kernel->terminate($request, $response);

    // We don't "send" the response as we need to return it to the Lambda. This means
    // headers will not be sent since this is not an actual HTTP Request. If you
    // need full HTTP support, use the actual `public/index.php` file instead.
    return $response->getOriginalContent();
};
