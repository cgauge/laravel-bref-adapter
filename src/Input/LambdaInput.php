<?php declare(strict_types=1);

namespace CustomerGauge\Bref\Input;

final class LambdaInput
{
    public $event;

    public function __construct(array $event)
    {
        $this->event = $event;
    }
}
