<?php

namespace App\Visitantes\Interfaces;

use App\Visitantes\Models\RespostaJson;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ControladorRest implements RequestHandlerInterface
{
    protected readonly RespostaJson $_501;
    protected readonly RespostaJson $_404;
    protected readonly RespostaJson $_405;

    public function __construct()
    {
        $this->_501 =  new RespostaJson(501, "Método não implementado");
        $this->_404 =  new RespostaJson(404, "Recurso não encontrado");
        $this->_405 =  new RespostaJson(405, "Método não permitido");
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        return match ($method) {
            "GET" => $this->get($request),
            "POST" => $this->post($request),
            "PUT" => $this->put($request),
            "DELETE" => $this->delete($request),
            default => $this->_405,
        };
    }


    abstract public function get(ServerRequestInterface $request): ResponseInterface;

    abstract public function post(ServerRequestInterface $request): ResponseInterface;

    abstract public function put(ServerRequestInterface $request): ResponseInterface;

    abstract public function delete(ServerRequestInterface $request): ResponseInterface;
}
