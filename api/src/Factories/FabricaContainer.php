<?php

namespace App\Visitantes\Factories;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Repositories\RepositorioUsuarioPDO;


class FabricaContainer
{
    public static function getContainer(): ?Container
    {
        $construtor = new ContainerBuilder();
        $construtor->addDefinitions([
            RepositorioUsuario::class => function () {
                return RepositorioUsuarioPDO::obterRepositorioUsuario();
            },
        ]);
        try {
            return $construtor->build();
        } catch (Exception) {
            return null;
        }
    }

}
