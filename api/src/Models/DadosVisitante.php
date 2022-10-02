<?php

namespace App\Visitantes\Models;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\Entidade;
use DateTime;

class DadosVisitante extends Entidade
{
    private string $cpf;
    private string $nome;
    private ?DateTime $data_nascimento;
    private ?string $identidade;
    private ?string $expedidor;
    private ?string $foto;
    private string $formatoData;

    /**
     * @param string $cpf
     * @param string $nome
     */
    public function __construct(string $cpf, string $nome)
    {
        $this->setCpf($cpf);
        $this->setNome($nome);
        $this->formatoData = Utils::FORMATOS_DATA['date'];
    }

    public static function validarCPF(string $cpf, bool $strict = false): bool|string
    {
        $cpf = str_replace(array('.', '-'), '', $cpf);

        if ((int)$cpf===0 || strlen($cpf) < 11) {
            return false;
        }

        if (!$strict) {return $cpf;}

        $soma = $resto= 0;

        for ($i = 1; $i <= 9; $i++) {
            $soma += ((int)$cpf[$i - 1]) * (11 - $i);
        }
        $resto = ($soma * 10) % 11;


        if (($resto === 10) | ($resto===11)) {
            $resto = 0;
        }

        if ($resto !== (int)($cpf[9])) {
            return false;
        }

        $soma = 0;
        for ($i = 1; $i <= 10; $i++) {
            $soma += ((int)$cpf[$i - 1]) * (12 - $i);
        }
        $resto = ($soma * 10) % 11;

        if (($resto === 10) | ($resto === 11)) {
            $resto = 0;
        }

        if ($resto !== ((int)$cpf[10])) {
            return false;
        }

        return $cpf;
    }

    public function paraArray(): array
    {
        $arr = array();
        $arr['cpf'] = $this->cpf;
        $arr['nome'] = strtoupper($this->nome);
        $arr['identidade'] = strtoupper($this->identidade);
        $arr['expedidor'] = strtoupper($this->expedidor);
        $arr['foto'] = $this->foto;
        $arr['data_nascimento'] = $this->data_nascimento?->format($this->formatoData);

        return $arr;
    }

    /**
     * @return string
     */
    public function getCpf(): string
    {
        return $this->cpf;
    }

    /**
     * @param ?string $cpf
     */
    public function setCpf(string $cpf): void
    {
        $_cpf = self::validarCPF($cpf);
        if ($_cpf === false) {
            return;
        }
        $this->cpf = $_cpf;
    }

    /**
     * @return string
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     */
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    /**
     * @return DateTime|null
     */
    public function getDataNascimento(): ?DateTime
    {
        return $this->data_nascimento;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $data_nascimento
     */
    public function setDataNascimento($data_nascimento): void
    {
        if ($data_nascimento instanceof DateTime) {
            $this->data_nascimento = $data_nascimento;
        } else {
            $this->data_nascimento = null;
        }
    }

    /**
     * @return string|null
     */
    public function getIdentidade(): ?string
    {
        return $this->identidade;
    }

    /**
     * @param string|null $identidade
     */
    public function setIdentidade(?string $identidade): void
    {
        $this->identidade = $identidade==="" ? null : $identidade;
    }

    /**
     * @return string|null
     */
    public function getExpedidor(): ?string
    {
        return $this->expedidor;
    }

    /**
     * @param string|null $expedidor
     */
    public function setExpedidor(?string $expedidor): void
    {
        $this->expedidor = $expedidor===" " ? null : $expedidor;
    }

    /**
     * @return string|null
     */
    public function getFoto(): ?string
    {
        return $this->foto;
    }

    /**
     * @param string|null $foto
     */
    public function setFoto(?string $foto): void
    {
        $this->foto = $foto==="" ? null : $foto;
    }

    /**
     * @return string
     */
    public function getFormatoData(): string
    {
        return $this->formatoData;
    }

    /**
     * @param string $formatoData
     * @return bool
     */
    public function setFormatoData(string $formatoData): bool
    {
        if (in_array($formatoData, Utils::FORMATOS_DATA, true)) {
            $this->formatoData = $formatoData;
            return true;
        }
        return false;
    }
}
