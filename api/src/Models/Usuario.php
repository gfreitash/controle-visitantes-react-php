<?php

namespace App\Visitantes\Models;

use App\Visitantes\Interfaces\Entidade;

class Usuario extends Entidade
{
    private ?int $id;
    private int $funcao;
    private string $nome;
    private string $email;
    private string $senha;
    private ?string $refreshToken;
    private ?bool $modificouSenha;

    /**
     * @param ?int $id
     * @param string $nome
     * @param string $email
     * @param string $senha
     */
    public function __construct(?int $id, int $funcao, string $nome, string $email, string $senha = "")
    {
        $this->id = $id;
        $this->funcao = $funcao;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->modificouSenha = null;
    }


    public function paraArray(): array
    {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['funcao'] = $this->funcao;
        $arr['nome'] = strtoupper($this->nome);
        $arr['email'] = strtolower($this->email);
        $arr['modificou_senha'] = $this->modificouSenha;

        return $arr;
    }

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param ?int $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getFuncao(): int
    {
        return $this->funcao;
    }

    /**
     * @param int $funcao
     */
    public function setFuncao(int $funcao): void
    {
        $this->funcao = $funcao;
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
     * @return ?bool
     */
    public function getModificouSenha(): ?bool
    {
        return $this->modificouSenha;
    }

    /**
     * @param ?bool $modificouSenha
     */
    public function setModificouSenha(?bool $modificouSenha): void
    {
        $this->modificouSenha = $modificouSenha;
    }
}
