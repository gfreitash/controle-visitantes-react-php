<?php

namespace App\Visitantes\Models;

use Nyholm\Psr7\Response;

class RespostaJson extends Response
{
    public function __construct(
        int $status = 200,
        $body = null,
        array $headers = [],
        string $version = '1.1',
        string $reason = null
    )
    {
        parent::__construct($status, ["Content-Type" => "application/json"] + $headers, $body, $version, $reason);
    }
}
