<?php

namespace App\Visitantes\Helpers;

use DateTime;

class Utils
{
    public const FORMATOS_DATA =
        ['datetime' => "Y-m-d H:i:s",
            'date' => "Y-m-d", 'web' => 'Y-m-d\TH:i:s',
            'datetime_local' => 'd/m/Y H:i:s',
            'datetime_local_curto' => 'd/m/Y H:i',
            'date_local' => 'd/m/Y'];

    public static function arrayOrdenacaoParaString(array $arrayAssoc): string
    {
        $append = "";
        foreach ($arrayAssoc as $chave => $valor) {
            if ($chave !== array_key_last($arrayAssoc)) {
                $append .= $chave . " " . $valor . ", ";
            } else {
                $append .= $chave . " " . $valor;
            }
        }

        return $append;
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

    public static function tentarCriarDateTime(?string $data): ?DateTime
    {
        if (!$data) {return null;}

        foreach (self::FORMATOS_DATA as $formato) {
            $retorno = DateTime::createFromFormat($formato, $data);
            if ($retorno) {
                return $retorno;
            }
        }
        return null;
    }

    public static function seNuloRetornarVazio($var): string
    {
        if (empty($var)) {
            return "";
        }
        return $var;
    }

    public static function converterBinarioParaBase64($uploadBinario, $mime): string
    {
        if (!$uploadBinario || !$mime) {
            return "";
        }
        return 'data:'.$mime.';base64,'.base64_encode($uploadBinario);
    }

    /**
     * Essa função é usada para popular uma variável global com os dados da requisição PUT.
     * Esse código foi obtido de: https://stackoverflow.com/a/18678678
     * @return void
     */
    public static function parsePut(): void
    {

        /* PUT data comes in on the stdin stream */
        $putdata = fopen("php://input", "r");

        $raw_data = '';

        /* Read the data 1 KB at a time
           and write to the file */
        while ($chunk = fread($putdata, 1024)) {
            $raw_data .= $chunk;
        }

        /* Close the streams */
        fclose($putdata);

        // Fetch content and determine boundary
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

        if (empty($boundary)) {
            parse_str($raw_data, $data);
            $GLOBALS[ '_PUT' ] = $data;
            return;
        }

        // Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = array();

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                $tmp_name = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, , $name) = $matches;

                //Parse File
                if (isset($matches[4])) {
                    //if labeled the same as previous, skip
                    if (isset($_FILES[$matches[2]])) {
                        continue;
                    }

                    //get filename
                    $filename = $matches[4];

                    //get tmp name
                    $filename_parts = pathinfo($filename);
                    $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);

                    //populate $_FILES with information, size may be off in multibyte situation
                    $_FILES[ $matches[ 2 ] ] = array(
                        'error'=>0,
                        'name'=>$filename,
                        'tmp_name'=>$tmp_name,
                        'size'=>strlen($body),
                        'type'=>$value
                    );

                    //place in temporary directory
                    file_put_contents($tmp_name, $body);
                } else { //Parse Field
                    $data[$name] = substr($body, 0, strlen($body) - 2);
                }
            }

        }
        $GLOBALS[ '_PUT' ] = $data;
    }
}
