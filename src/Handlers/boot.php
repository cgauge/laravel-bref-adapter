<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../../../../autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../../../../../bootstrap/app.php';

return $app;
