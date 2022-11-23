<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\Observacao;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\Visita;

interface RepositorioObservacao
{
    public function criar(Observacao $observacao): bool|int;

    public function buscarPorId(int $id): bool|Observacao;

    public function atualizar(Observacao $observacao): bool;

    public function buscarTodasDeVisita(Visita $visita, ParametroBusca $parametros=null): array;

    public function obterTotalDeVisita(Visita $visita, ParametroBusca $parametros=null): int;

    public static function obterRepositorioObservacao(): RepositorioObservacao;

}
