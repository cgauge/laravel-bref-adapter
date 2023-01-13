<?php declare(strict_types=1);

namespace CustomerGauge\Bref;

use Throwable;

interface AlwaysReportExceptionHandler
{
    public function alwaysReport(Throwable $e): void;
}