<?php
require_once __DIR__.'/../vendor/autoload.php';

/* Carregando as variáveis de ambiente */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

//** Origens permitidas */
define(
    "ORIGENS",
    [
        "http://localhost:3000",
        "{$_ENV['PROTOCOLO']}://{$_SERVER['SERVER_ADDR']}:{$_ENV['PORTA_CORS']}",
        "{$_ENV['PROTOCOLO']}://{$_ENV['IP_SERVIDOR']}:{$_ENV['PORTA_CORS']}"
    ]
) ;

/** O nome do banco de dados*/
define("DB_NAME", $_ENV['DB_NAME']);
/** Usuário do banco de dados MySQL */
define("DB_USER", $_ENV['DB_USER']);
/** Senha do banco de dados MySQL */
define("DB_PASSWORD", $_ENV['DB_PASSWORD']);
/** nome do host do MySQL */
define("DB_HOST", $_ENV['DB_HOST']);

/** configuração de acesso do banco de dados via DataLayer */
const DATA_LAYER_CONFIG = [
    "driver" => "mysql",
    "host" => DB_HOST,
    "port" => "3306",
    "dbname" => DB_NAME,
    "username" => DB_USER,
    "passwd" => DB_PASSWORD,
    "options" => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
];

/** Diretório onde as fotos de visitantes são salvas */
const DIR_FOTOS = '/assets/imgs/fotos/';

//Definindo o timezone padrão
date_default_timezone_set('America/Sao_Paulo');

//Definindo o locale padrão para pt_BR
$locale = 'pt_BR';
setlocale(LC_ALL, $locale, 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
\PhpOffice\PhpSpreadsheet\Settings::setLocale($locale);
