<?php

namespace Jot\HfShield\Exception;

use function Hyperf\Translation\__;

class InvalidOtpCodeException extends AbstractException
{

    public function __construct(string $message, array $metadata = [])
    {
        $this->metadata = $metadata;
        $this->message = __($message);
        $this->code = 400;
        parent::__construct($this->message, $this->code);
    }

}