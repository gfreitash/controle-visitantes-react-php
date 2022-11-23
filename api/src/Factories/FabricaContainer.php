<?php

namespace App\Visitantes\Factories;

use App\Visitantes\Interfaces\RepositorioObservacao;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Repositories\RepositorioObservacaoPDO;
use App\Visitantes\Repositories\RepositorioVisitantePDO;
use App\Visitantes\Repositories\RepositorioVisitaPDO;
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
            RepositorioVisita::class => function () {
                return RepositorioVisitaPDO::obterRepositorioVisita();
            },
            RepositorioObservacao::class => function () {
                return RepositorioObservacaoPDO::obterRepositorioObservacao();
            }
        ]);
        try {
            return $construtor->build();
        } catch (Exception) {
            return null;
        }
    }

}
