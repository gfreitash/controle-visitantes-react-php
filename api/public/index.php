<?php

use App\Visitantes\Factories\FabricaRequest;
use App\Visitantes\Routes\GerenciadorRotas;
use App\Visitantes\Routes\Rotas;

require_once __DIR__ . "/../config/config.php";

//Checa se há alguma origem exterior tentando acessar o servidor
//Se sim, configura os cabeçalhos da resposta do CORS
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    $origem = in_array($_SERVER['HTTP_ORIGIN'], ORIGENS, true) ?
        $_SERVER['HTTP_ORIGIN']
        : ORIGENS[0];
    $maxAge = 60 * 60 * 24; // 1 dia

    http_response_code(200);
    header("Access-Control-Allow-Origin: $origem"); //Origem permitida
    header("Access-Control-Allow-Headers: Authorization"); //Cabeçalhos permitidos
    header("Access-Control-Allow-Credentials: true"); //Credenciais são permitidas
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); //Métodos permitidos
    header("Access-Control-Max-Age: $maxAge"); //Tempo de cache

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}


$request = FabricaRequest::createRequestFromGlobals();
$caminho = $_SERVER['PATH_INFO'];
$autorizacao = match (Rotas::ROTAS[$caminho]['autorizacao']) {
    Rotas::AUTORIZACAO[1] => $request->getHeader('Authorization')[0] ?? "",
    Rotas::AUTORIZACAO[2] => $request->getCookieParams()['jwt'] ?? "",
    default => "",
};

$gerenciadorRotas = new GerenciadorRotas();
$controlador = $gerenciadorRotas->obterControladorRota($caminho, $autorizacao);

if (!$controlador) {
    http_response_code($gerenciadorRotas->obterCodErro());
    header("Erro: {$gerenciadorRotas->obterCodErro()}: {$gerenciadorRotas->obterErro()}");
    exit();
}

$resposta = $controlador->handle($request);
foreach ($resposta->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
http_response_code($resposta->getStatusCode());

echo $resposta->getBody();
