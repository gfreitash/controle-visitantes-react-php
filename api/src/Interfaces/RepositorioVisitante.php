<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\Visitante;

interface RepositorioVisitante
{
    public function adicionarVisitante(Visitante $visitante): bool|int;

    public function buscarPorCpf(string $cpf): bool|Visitante;

    public function buscarPorId(string $id): bool|Visitante;

    public function alterarVisitante(Visitante $visitante, $cpf_antigo=false): bool;

    public function removerVisitantePorCPF(string $cpf): bool;

    public function buscarTodosVisitantes($ordenar_por=false, int $limit=0, int $offset=0): array;

    public function buscarVisitantesComo($termo, $ordenar_por=false, int $limit=0, int $offset=0): array;

    public function obterTotalVisitantes(): int;

    public static function obterRepositorioVisitante(): RepositorioVisitante;

}
