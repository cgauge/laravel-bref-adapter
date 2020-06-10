<?php

namespace CustomerGauge\Bref\Helpers;

use Illuminate\Foundation\Application;

/**
 * @see https://github.com/laravel/vapor-core/blob/2.0/src/Runtime/StorageDirectories.php
 */
class StorageDirectories
{
    /**
     * The storage path for the execution environment.
     *
     * @var string
     */
    public const PATH = '/tmp/laravel-bref-adapter/storage';

    /**
     * Ensure the necessary storage directories exist.
     *
     * @return void
     */
    public static function create(Application $app)
    {
        $directories = [
            self::PATH.'/app',
            self::PATH.'/cache',
            self::PATH.'/framework/cache',
            self::PATH.'/framework/views',
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        $app->useStoragePath(self::PATH);
    }
}
