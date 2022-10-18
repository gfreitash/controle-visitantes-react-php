<?php

namespace App\Visitantes\Interfaces;

use CoffeeCode\DataLayer\DataLayer;

abstract class JoinableDataLayer extends DataLayer
{
    private string $entity;

    public function __construct(
        string $entity,
        array $required,
        string $primary = 'id',
        bool $timestamps = true,
        array $database = null
    ) {
        parent::__construct($entity, $required, $primary, $timestamps, $database);
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function findWithJoin(
        string $join_entity,
        string $join_entity_field,
        string $join_field,
        ?string $terms = null,
        ?string $params = null,
        string $columns = "*"
    ): DataLayer
    {
        $this->statement =
            "SELECT $columns "
            ."FROM $this->entity "
            ."LEFT JOIN $join_entity "
            ."ON $join_entity.$join_entity_field = $this->entity.$join_field ";

        if ($terms) {
            $this->statement .= "WHERE {$terms}";
            parse_str($params, $this->params);
        }

        return $this;
    }
}
