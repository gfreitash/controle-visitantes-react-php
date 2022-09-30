<?php

namespace App\Visitantes\Interfaces;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ControladorRest implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        return match ($method) {
            "GET" => $this->get($request),
            "POST" => $this->post($request),
            "PUT" => $this->put($request),
            "DELETE" => $this->delete($request),
            default => new Response(405),
        };
    }


    abstract public function get(ServerRequestInterface $request): ResponseInterface;

    abstract public function post(ServerRequestInterface $request): ResponseInterface;

    abstract public function put(ServerRequestInterface $request): ResponseInterface;

    abstract public function delete(ServerRequestInterface $request): ResponseInterface;
}
