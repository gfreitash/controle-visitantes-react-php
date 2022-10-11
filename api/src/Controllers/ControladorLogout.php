<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\JwtHelper;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorLogout extends ControladorRest
{
    public function __construct(private readonly RepositorioUsuario $repositorioUsuario)
    {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(405);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $token = JwtHelper::decodeAccessToken($request->getHeader("Authorization")[0]) ?? null;
        if (!$token) {
            return new Response(401);
        }
        $usuario = $this->repositorioUsuario->buscarPor("id", $token?->id);
        if (!$usuario) {
            return new Response(401);
        }

        $usuario->setRefreshToken(null);
        $this->repositorioUsuario->alterarUsuario($usuario);
        return new Response(200, ["Set-Cookie" => "jwt=; Max-Age=0"]);
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
