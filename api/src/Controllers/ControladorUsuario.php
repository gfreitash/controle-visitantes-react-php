<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Models\RespostaJson;
use Nyholm\Psr7\Response;
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
        return $this->_501;
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }
}
