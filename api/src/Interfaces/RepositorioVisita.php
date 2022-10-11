<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\Visita;
use App\Visitantes\Models\Visitante;

interface RepositorioVisita
{
    public function adicionarVisita(Visita $visita): bool|Visita;

    public function buscarPorId(int $id): bool|Visita;

    public function alterarVisita(Visita $visita): bool;

    public function removerVisitaPorId(int $id): bool;

    public function obterTodasVisitas(string $status="", $ordenarPor=false, int $limite=0, int $offset=0): array;

    public function obterTodasVisitasDeVisitante(
        Visitante $visitante,
        string $status="",
        $ordenarPor=false,
        int $limite=0,
        int $offset=0
    ): array;

    public static function obterRepositorioVisita(): RepositorioVisita;

}
