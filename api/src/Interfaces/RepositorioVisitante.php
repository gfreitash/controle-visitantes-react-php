<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\Visitante;

interface RepositorioVisitante
{
    public function criar(Visitante $visitante): bool|int;

    public function buscarPorCpf(string $cpf): bool|Visitante;

    public function buscarPorId(string $id): bool|Visitante;

    public function atualizar(Visitante $visitante): bool;

    public function removerPorCpf(string $cpf): bool;

    public function buscarTodos(ParametroBusca $parametros = null): array;

    public function buscarComo($termo, ParametroBusca $parametros = null): array;

    public function obterTotal(string $como=""): int;

    public static function obterRepositorioVisitante(): RepositorioVisitante;

}
