<?php

namespace App\Visitantes\Routes;

use App\Visitantes\Controllers\ControladorLogin;
use App\Visitantes\Controllers\ControladorLogout;
use App\Visitantes\Controllers\ControladorRefresh;
use App\Visitantes\Controllers\ControladorRelatorio;
use App\Visitantes\Controllers\ControladorUsuario;
use App\Visitantes\Controllers\ControladorVisita;
use App\Visitantes\Controllers\ControladorVisitante;

class Rotas
{
    public const AUTORIZACAO = ["nenhuma", "accessToken", "refreshToken"];
    public const ROTAS = [
        "/login" => [
            "classe" => ControladorLogin::class,
            "autorizacao" => self::AUTORIZACAO[0],
        ],
        "/refresh" => [
            "classe" => ControladorRefresh::class,
            "autorizacao" => self::AUTORIZACAO[2],
        ],
        "/logout" => [
            "classe" => ControladorLogout::class,
            "autorizacao" => self::AUTORIZACAO[1],
        ],
        "/visitante" => [
            "classe" => ControladorVisitante::class,
            "autorizacao" => self::AUTORIZACAO[1],
        ],
        "/usuario" => [
            "classe" => ControladorUsuario::class,
            "autorizacao" => self::AUTORIZACAO[1],
        ],
        "/visita" => [
            "classe" => ControladorVisita::class,
            "autorizacao" => self::AUTORIZACAO[1],
        ],
        "/relatorio" => [
            "classe" => ControladorRelatorio::class,
            "autorizacao" => self::AUTORIZACAO[1],
        ],
    ];
}
