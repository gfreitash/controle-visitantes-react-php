<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\Visitante;

interface RepositorioVisitante
{
    public function adicionarVisitante(Visitante $visitante): bool|int;

    public function buscarPorCpf(string $cpf): bool|Visitante;

    public function buscarPorId(string $id): bool|Visitante;

    public function alterarVisitante(Visitante $visitante): bool;

    public function removerVisitantePorCPF(string $cpf): bool;

    public function buscarTodosVisitantes(ParametroBusca $parametros = null): array;

    public function buscarVisitantesComo($termo, ParametroBusca $parametros = null): array;

    public function obterTotalVisitantes(string $como=""): int;

    public static function obterRepositorioVisitante(): RepositorioVisitante;

}
