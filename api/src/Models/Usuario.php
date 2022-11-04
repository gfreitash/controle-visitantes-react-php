<?php

namespace App\Visitantes\Models;

use DateTime;
use App\Visitantes\Interfaces\Entidade;

class Usuario extends Entidade
{
    private int $id;
    private string $nome;
    private string $email;
    private string $senha;
    private ?string $refreshToken;
    private ?DateTime $dataUltimaMod;
    private ?bool $modificouSenha;

    /**
     * @param int $id
     * @param string $nome
     * @param string $email
     * @param string $senha
     */
    public function __construct(int $id, string $nome, string $email, string $senha = "")
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->modificouSenha = null;
    }


    public function paraArray(): array
    {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['nome'] = strtoupper($this->nome);
        $arr['email'] = strtolower($this->email);
        $arr['modificou_senha'] = $this->modificouSenha;

        !empty($this->dataUltimaMod) ?
            $arr['data_ultima_mod'] = $this->dataUltimaMod->format("d/m/Y"):
            $arr['data_ultima_mod'] = null;

        return $arr;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getDataUltimaMod(): ?DateTime
    {
        return $this->dataUltimaMod;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $dataUltimaMod
     */
    public function setDataUltimaMod($dataUltimaMod): void
    {
        if ($dataUltimaMod instanceof DateTime) {
            $this->dataUltimaMod = $dataUltimaMod;
        } else {
            $this->dataUltimaMod = null;
        }
    }

    /**
     * @return string
     */
    public function getSenha(): string
    {
        return $this->senha;
    }

    /**
     * @param string $senha
     */
    public function setSenha(string $senha): void
    {
        $this->senha = $senha;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     */
    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return bool
     */
    public function isModificouSenha(): bool
    {
        return $this->modificouSenha;
    }

    /**
     * @param bool $modificouSenha
     */
    public function setModificouSenha(bool $modificouSenha): void
    {
        $this->modificouSenha = $modificouSenha;
    }
}
