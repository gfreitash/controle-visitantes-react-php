<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\Usuario;

interface RepositorioUsuario
{

    public function buscarPorId(string $id) : bool|Usuario;

    public function buscarPor(string $campo, string $valor) : bool|Usuario;

    public function criar(Usuario $usuario) : bool|int;

    public function atualizar(Usuario $usuario): bool;

    public static function obterRepositorioUsuario(): RepositorioUsuario;
}
