<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\Visita;
use App\Visitantes\Models\Visitante;

interface RepositorioVisita
{
    public function criar(Visita $visita): bool|Visita;

    public function buscarPorId(int $id): bool|Visita;

    public function atualizar(Visita $visita): bool;

    public function removerPorId(int $id): bool;

    public function buscarTodas(
        string $status="",
        ParametroBusca $parametros = null
    ): array;

    public function buscarTodasDeVisitante(
        Visitante $visitante,
        string $status="",
        ParametroBusca $parametros=null
    ): array;

    public function buscarVisitantesAtivos(?ParametroBusca $parametros = null): array;

    public function obterTotal(?string $status=""): int;

    public static function obterRepositorioVisita(): RepositorioVisita;

}
