<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Models\RespostaJson;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorUsuario extends ControladorRest
{

    public function __construct(private readonly RepositorioUsuario $repositorioUsuario)
    {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getQueryParams()['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            return new RespostaJson(400, json_encode(['error' => 'ID não informado ou inválido'])
            );
        }

        $usuario = $this->repositorioUsuario->buscarPorId($id);
        if (!$usuario) {
            return new RespostaJson(404, json_encode(['error' => 'Usuário não encontrado']));
        }

        return new RespostaJson(200, json_encode($usuario));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        global $_PUT;
        $dados = $_PUT;

        if (!isset($dados['id']) || !is_numeric($dados['id'])) {
            return new RespostaJson(400, json_encode(['error' => 'ID não informado ou inválido']));
        } elseif (!isset($dados['senhaAtual']) || !isset($dados['novaSenha'])) {
            return new RespostaJson(400, json_encode(['error' => 'Senha atual e nova senha devem ser informadas']));
        } elseif (strlen($dados['novaSenha']) < 8) {
            return new RespostaJson(400, json_encode(['error' => 'A nova senha deve ter no mínimo 8 caracteres']));
        } elseif ($dados['senhaAtual'] === $dados['novaSenha']) {
            return new RespostaJson(400, json_encode(['error' => 'A nova senha deve ser diferente da senha atual']));
        } elseif ($dados['novaSenha'] !== $dados['confirmarNovaSenha']) {
            return new RespostaJson(
                400,
                json_encode(['error' => 'A nova senha e a confirmação da nova senha devem ser iguais'])
            );
        }

        $usuario = $this->repositorioUsuario->buscarPorId($dados['id']);
        if (!$usuario) {
            return new RespostaJson(404, json_encode(['error' => 'Usuário não encontrado']));
        } elseif (!password_verify($dados['senhaAtual'], $usuario->getSenha())) {
            return new RespostaJson(400, json_encode(['error' => 'Senha atual incorreta']));
        }

        $hash = password_hash($dados['novaSenha'], PASSWORD_DEFAULT, ['cost' => 13]);
        $usuario->setSenha($hash);
        $resultado = $this->repositorioUsuario->alterarUsuario($usuario);
        if ($resultado) {
            return new RespostaJson(200, json_encode($this->repositorioUsuario->buscarPorId($dados['id'])));
        }

        return new RespostaJson(500, json_encode(['error' => 'Erro interno do servidor']));
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }
}
