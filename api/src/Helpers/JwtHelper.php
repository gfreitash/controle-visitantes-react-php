<?php

namespace App\Visitantes\Helpers;

use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Models\Usuario;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nyholm\Psr7\Response;

class JwtHelper
{
    private const ALGORITMO = "HS256";
    private const TEMPO_VIDA_ACCESS_TOKEN = 60 * 3; // 3 minutos
    private const TEMPO_VIDA_REFRESH_TOKEN = 60 * 60 * 24; // 1 dia

    public static function criarAccessToken(array $conteudo): ?string
    {
        $payload = [
            "iat" => time(),
            "exp" => time() + self::TEMPO_VIDA_ACCESS_TOKEN
        ];

        try {
            foreach ($conteudo as $chave => $valor) {
                $payload[$chave] = $valor;
            }
            return JWT::encode($payload, $_ENV["ACCESS_TOKEN_KEY"], self::ALGORITMO);
        } catch (\Exception) {
            return null;
        }
    }

    public static function decodeAccessToken(string $token): ?\stdClass
    {
        try {
            $token = str_replace("Bearer ", "", $token);
            return JWT::decode($token, new Key($_ENV["ACCESS_TOKEN_KEY"], self::ALGORITMO));
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
            return null;
        }
    }

    public static function criarRefreshToken(array $conteudo): ?string
    {
        $payload = [
            "iat" => time(),
            "exp" => time() + self::TEMPO_VIDA_REFRESH_TOKEN
        ];

        try {
            foreach ($conteudo as $chave => $valor) {
                $payload[$chave] = $valor;
            }
            return JWT::encode($payload, $_ENV["REFRESH_TOKEN_KEY"], self::ALGORITMO);
        } catch (\Exception) {
            return null;
        }
    }

    public static function decodeRefreshToken(string $token): \stdClass
    {
       return JWT::decode($token, new Key($_ENV["REFRESH_TOKEN_KEY"], self::ALGORITMO));
    }

    public static function criarRespostaAutenticacao(
        array $conteudoToken,
        Usuario $usuario,
        RepositorioUsuario $repositorioUsuario
    ): Response {

        $accessToken = self::criarAccessToken($conteudoToken);
        $refreshToken = self::criarRefreshToken($conteudoToken);
        $usuario->setRefreshToken($refreshToken);
        $repositorioUsuario->alterarUsuario($usuario);

        $dt = new \DateTime();
        $dt->setTimestamp(JwtHelper::decodeRefreshToken($refreshToken)->exp);
        $dt->setTimezone(new \DateTimeZone("GMT"));
        $cookieMaxAge = $dt->format(DATE_COOKIE);

        $corpoResposta = $conteudoToken;
        $corpoResposta["accessToken"] = $accessToken;
        return new Response(
            200,
            ["Set-Cookie" => "jwt=$refreshToken; Expires=$cookieMaxAge; Path=/; HttpOnly; SameSite=Lax"],
            json_encode($corpoResposta)
        );
    }
}
