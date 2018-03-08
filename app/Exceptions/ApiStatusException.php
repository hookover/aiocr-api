<?php

namespace App\Exceptions;

class ApiStatusException extends ApiException
{
    public function __construct($status_code = 500, $http_code = 500)
    {
        parent::__construct($this->getMessageByStatus($status_code));

        $this->http_code = $http_code;

        $this->status_code = $status_code;
    }
}
