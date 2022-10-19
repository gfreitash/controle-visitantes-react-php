<?php

namespace App\Visitantes\Models;

use DateTime;

class ParametroBusca
{
    public function __construct(
        public array $ordenarPor = [],
        public int $limite = 0,
        public int $offset = 0,
        public ?DateTime $dataInicio = null,
        public ?DateTime  $dataFim = null
    ) {
    }
}
