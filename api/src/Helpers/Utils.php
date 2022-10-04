<?php

namespace App\Visitantes\Helpers;

use DateTime;

class Utils
{
    public const FORMATOS_DATA =
        ['datetime' => "Y-m-d H:i:s",
            'date' => "Y-m-d", 'web' => 'Y-m-d\TH:i:s',
            'datetime_local_curto' => 'd/m/Y H:i',
            'date_local' => 'd/m/Y'];


    public static function obterHoraDataLocal(): DateTime
    {
        // DEFINE O FUSO HORÁRIO COMO O HORÁRIO DE BRASILIA
        date_default_timezone_set('America/Sao_Paulo');
        // CRIA UMA VARIAVEL E ARMAZENA A HORA ATUAL DO FUSO-HORÁRIO DEFINIDO (BRASÍLIA)
        $dataLocal = date('d/m/Y');
        $timestamp = mktime(date("H") - 3, date("i"), 0);
        $hr = gmdate("H:i:s", $timestamp);
        return DateTime::createFromFormat('d/m/Y H:i:s', $dataLocal.' '.$hr);
    }

    public static function formatarCPF(string $cpf): string
    {
        if (strlen($cpf) < 11) {
            return $cpf;
        }

        $parte1 = substr($cpf, 0, 3) . ".";
        $parte2 = substr($cpf, 3, 3) . ".";
        $parte3 = substr($cpf, 6, 3) . "-";
        $parte4 = substr($cpf, 9, 2);

        return $parte1.$parte2.$parte3.$parte4;
    }

    public static function formatarData($data, string $formatoEntrada, string $formatoSaida): string
    {
        if ($data !== null
            && in_array($formatoEntrada, self::FORMATOS_DATA, true)
            && in_array($formatoSaida, self::FORMATOS_DATA, true)) {

            $dt = DateTime::createFromFormat($formatoEntrada, $data);
            if ($dt) {
                return $dt->format($formatoSaida);
            }
        }

        return "";
    }
}
