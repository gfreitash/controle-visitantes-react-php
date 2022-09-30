<?php

namespace App\Visitantes\Models;

use PDO;

class Conexao
{
    public static function criarConexao(): PDO
    {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME,
            DB_USER,
            DB_PASSWORD
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        return $pdo;
    }
}
