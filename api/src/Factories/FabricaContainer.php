<?php

namespace App\Visitantes\Factories;

use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Repositories\RepositorioVisitantePDO;
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
            RepositorioVisitante::class => function () {
                return RepositorioVisitantePDO::obterRepositorioVisitante();
            },
        ]);
        try {
            return $construtor->build();
        } catch (Exception) {
            return null;
        }
    }

}
