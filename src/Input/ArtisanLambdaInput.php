<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Input;

use Symfony\Component\Console\Input\ArgvInput;

final class ArtisanLambdaInput extends ArgvInput
{
    public function __construct()
    {
        $input = $this->input();

        parent::__construct($input);
    }

    /**
     * Define which Artisan Command we're executing by looking at the ARTISAN_COMMAND
     * Environment Variable. This variable should always be set by a serverless.yaml
     * template when defining a PHP cli command.
     */
    private function input()
    {
        if (isset($_ENV['ARTISAN_COMMAND'])) {
            return $this->parseInput($_ENV['ARTISAN_COMMAND']);
        }

        if (isset($_SERVER['ARTISAN_COMMAND'])) {
            return $this->parseInput($_SERVER['ARTISAN_COMMAND']);
        }

        return $_SERVER['argv'];
    }

    private function parseInput(string $input): array
    {
        $split = explode(' ', $input);

        // Symfony\Component\Console\Input\ArgvInput will remove the first item of the array
        // and ignore it. This is because any cli command using Symfony console starts from
        // `php` executable and run a specific file. The file name is the very first arg
        // and is ignored by Symfony Console. Since we're starting from Lambda Function
        // We need to add a fake parameter (e.g. 'lambda') so that it can be taken out.
        array_unshift($split, 'lambda');

        return $split;
    }
}
