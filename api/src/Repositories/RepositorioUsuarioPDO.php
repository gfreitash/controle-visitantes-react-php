<?php

namespace App\Visitantes\Repositories;

use CoffeeCode\DataLayer\DataLayer;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Models\Usuario;

class RepositorioUsuarioPDO extends DataLayer implements RepositorioUsuario
{

    public function __construct()
    {
        parent::__construct("tb_usuario", ["nome", "email", "senha"], "id", false);
    }


    public function buscarPorId(string $id): bool|Usuario
    {
        $dados = $this->findById($id);

        if ($dados) {
            $usuario = new Usuario($id, $dados->nome, $dados->email, $dados->senha);
            $usuario->setDataUltimaMod($dados->data_ultima_mod);
            $usuario->setRefreshToken($dados->refresh_token);
            return $usuario;
        }

        return false;
    }

    public function alterarUsuario(Usuario $usuario): bool
    {
        $usr = $this->findById($usuario->getId());
        if (!$usr) {
            return false;
        }
        $this->id = $usuario->getId();
        $this->nome = $usuario->getNome();
        $this->email = $usuario->getEmail();
        $this->data_ultima_mod = $usuario->getDataUltimaMod();
        $this->senha = empty($usuario->getSenha()) ? $usr->senha : $usuario->getSenha();
        $this->refresh_token = $usuario->getRefreshToken();

        return $this->save();
    }

    public static function obterRepositorioUsuario(): RepositorioUsuario
    {
        return new RepositorioUsuarioPDO();
    }

    public function buscarPor(string $campo, string $valor): bool|Usuario
    {
        $dados = $this->find("$campo = :$campo", "$campo=$valor")->fetch();
        if (!$dados) {
            return false;
        }
        $usuario = new Usuario($dados->id, $dados->nome, $dados->email, $dados->senha);
        $usuario->setDataUltimaMod($dados->data_ultima_mod);
        $usuario->setRefreshToken($dados->refresh_token);
        return $usuario;

    }
}
