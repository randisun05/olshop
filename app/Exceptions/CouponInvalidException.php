<?php

namespace App\Exceptions;

use Exception;

class CouponInvalidException extends Exception
{
    public function __construct()
    {
        parent::__construct('Kode kupon tidak valid, sudah kedaluwarsa, atau tidak memenuhi syarat minimum belanja.');
    }
}
