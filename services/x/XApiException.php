<?php
declare(strict_types=1);

namespace Xinng\X;

final class XApiException extends \RuntimeException
{
    public int $status;

    public function __construct(int $status, string $message)
    {
        parent::__construct($message);
        $this->status = $status;
    }
}