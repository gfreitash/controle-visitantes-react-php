<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Interfaces\JoinableDataLayer;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Models\Usuario;

class RepositorioUsuarioPDO extends JoinableDataLayer implements RepositorioUsuario
{

    public function __construct()
    {
        parent::__construct("tb_usuario", ["funcao", "nome", "email", "senha"], "id", false);
    }

    public function criarUsuario(Usuario $usuario): bool|int
    {
        $arr = $usuario->paraArray();
        $arr['senha'] = $usuario->getSenha();

        $this->atribuirPropriedades($arr);
        if ($this->save()) {
            return $this->buscarPorId($this->id)?->getId() ?? false;
        }

        return false;
    }


    public function buscarPorId(string $id): bool|Usuario
    {
        $dados = $this->findById($id);

        if ($dados) {
            $usuario = new Usuario($id, $dados->funcao, $dados->nome, $dados->email, $dados->senha);
            $usuario->setRefreshToken($dados->refresh_token);
            $usuario->setModificouSenha($dados->modificou_senha);
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
        $this->funcao = $usuario->getFuncao();
        $this->nome = $usuario->getNome();
        $this->email = $usuario->getEmail();
        $this->senha = empty($usuario->getSenha()) ? $usr->senha : $usuario->getSenha();
        $this->refresh_token = $usuario->getRefreshToken();
        $this->modificou_senha = empty($usuario->getModificouSenha())
            ? $usr->modificou_senha
            : $usuario->getModificouSenha();

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
        $usuario = new Usuario($dados->id, $dados->funcao, $dados->nome, $dados->email, $dados->senha);
        $usuario->setRefreshToken($dados->refresh_token);
        $usuario->setModificouSenha($dados->modificou_senha);

        return $usuario;

    }
}
