<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * خطای ارسال پیامک از پنل ستارگان (srscrm.ir).
 * کد خطا همان مقدار فیلد code در پاسخ API است.
 */
class SmsException extends RuntimeException
{
    public function __construct(string $message, private readonly string $errorCode = 'error')
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
