<?php

namespace App\Visitantes\Interfaces;

abstract class Entidade implements \JsonSerializable
{
    abstract public function paraArray(): array;

    public function jsonSerialize(): array
    {
        return $this->paraArray();
    }
}
