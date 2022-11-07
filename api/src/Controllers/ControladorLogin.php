<?php

namespace App\Visitantes\Controllers;

use Nyholm\Psr7\Response;
use App\Visitantes\Helpers\JwtHelper;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorLogin extends ControladorRest
{
    public function __construct(private readonly RepositorioUsuario $repositorioUsuario)
    {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->post($request);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $email = filter_var($request->getParsedBody()['email'], FILTER_VALIDATE_EMAIL);
        $senha = htmlspecialchars($request->getParsedBody()['senha']);

        if (is_null($email) || $email === false || empty($senha)) {
            return new Response(400);
        }

        $usuario = $this->repositorioUsuario->buscarPor("email", $email);
        if (!$usuario) {
            return new Response(401);
        }

        $senhaEstaCorreta = password_verify($senha, $usuario->getSenha());
        if (!$senhaEstaCorreta) {
            return new Response(401);
        }

        $conteudo = [
            "nome" => $usuario->getNome(),
            "email" => $usuario->getEmail(),
            "funcao" => $usuario->getFuncao(),
            "id" => $usuario->getId()
        ];

        return JwtHelper::criarRespostaAutenticacao($conteudo, $usuario, $this->repositorioUsuario);
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(405);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(405);
    }
}
