<?php

namespace App\Visitantes\Controllers;

use Nyholm\Psr7\Response;
use App\Visitantes\Helpers\JwtHelper;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Repositories\RepositorioUsuarioPDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorRefresh extends ControladorRest
{
    public function __construct(private readonly RepositorioUsuarioPDO $repositorioUsuario)
    {}

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $refreshToken = $request->getCookieParams()['jwt'] ?? null;
        if (!$refreshToken) {
            return new Response(401);
        }
        $decoded = JwtHelper::decodeRefreshToken($refreshToken);
        $usuario = $this->repositorioUsuario->buscarPor("email", $decoded->email);

        if (!$usuario) {
            return new Response(403);
        }
        if ($usuario->getRefreshToken() !== $refreshToken) {
            $usuario->setRefreshToken(null);
            $this->repositorioUsuario->alterarUsuario($usuario);

            return new Response(403, ["Set-Cookie" => "jwt=; Max-Age=0"]);
        }

        $conteudoToken = [
            "nome" => $usuario->getNome(),
            "email" => $usuario->getEmail(),
            "id" => $usuario->getId()
        ];

        return JwtHelper::criarRespostaAutenticacao($conteudoToken, $usuario, $this->repositorioUsuario);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(405);
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
