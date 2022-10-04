<?php

namespace App\Visitantes\Routes;

use App\Visitantes\Factories\FabricaContainer;
use App\Visitantes\Helpers\JwtHelper;
use Psr\Http\Server\RequestHandlerInterface;

class GerenciadorRotas
{
    private string $codErro = "";
    private string $erro = "";

    public function obterCodErro(): string
    {
        return $this->codErro;
    }
    public function obterErro(): string
    {
        return $this->erro;
    }

    private function isAutorizacaoValida(?string $token, ?string $tipoToken): bool
    {
        if (!in_array($tipoToken, Rotas::AUTORIZACAO, true)) {
            return false;
        }
        if (!$token) {
            return $tipoToken === Rotas::AUTORIZACAO[0];
        }

        return match ($tipoToken) {
            Rotas::AUTORIZACAO[1] => JwtHelper::decodeAccessToken($token)?->exp > time(),
            Rotas::AUTORIZACAO[2] => JwtHelper::decodeRefreshToken($token)?->exp > time(),
            default => false,
        };
    }

    public function isRotaValida(string $rota): bool
    {
        return array_key_exists($rota, Rotas::ROTAS);
    }
    public function isRotaProtegida(string $rota): bool
    {
        return Rotas::ROTAS[$rota]['autorizacao'] !== Rotas::AUTORIZACAO[0];
    }

    public function obterControladorRota(?string $rota, string $token=null): ?RequestHandlerInterface
    {
        if (!$rota || !self::isRotaValida($rota)) {
            $this->codErro = "404";
            $this->erro = "Rota não encontrada";
            return null;
        }
        if (self::isRotaProtegida($rota) && !self::isAutorizacaoValida($token, Rotas::ROTAS[$rota]['autorizacao'])) {
            $this->codErro = "401";
            $this->erro = "Acesso não autorizado";
            return null;
        }

        try {
            $container = FabricaContainer::getContainer();
            $controlador = $container->get(Rotas::ROTAS[$rota]['classe']);
            $this->erro = "";
            $this->codErro = "";
            return $controlador;
        } catch (\Exception) {
            $this->codErro = "500";
            $this->erro = "Erro interno do servidor";
            return null;
        }
    }
}
