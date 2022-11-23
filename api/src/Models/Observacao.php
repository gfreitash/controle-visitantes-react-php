<?php

namespace App\Visitantes\Models;

use App\Visitantes\Interfaces\Entidade;
use DateTime;

class Observacao extends Entidade
{
    private ?int $id;
    private int $visitaId;
    private string $observacao;
    private DateTime $adicionadaEm;
    private int $adicionadaPor;
    private ?DateTime $modificadaEm;
    private ?int $modificadaPor;

    /**
     * @param int $visitaId
     * @param string $observacao
     * @param DateTime $adicionadaEm
     * @param int $adicionadaPor
     */
    public function __construct(int $visitaId, string $observacao, DateTime $adicionadaEm, int $adicionadaPor)
    {
        $this->id = null;
        $this->visitaId = $visitaId;
        $this->observacao = $observacao;
        $this->adicionadaEm = $adicionadaEm;
        $this->adicionadaPor = $adicionadaPor;
        $this->modificadaEm = null;
        $this->modificadaPor = null;
    }


    public function paraArray(): array
    {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['visita_id'] = $this->visitaId;
        $arr['observacao'] = $this->observacao;
        $arr['adicionada_em'] = $this->adicionadaEm->format('Y-m-d H:i:s');
        $arr['adicionada_por'] = $this->adicionadaPor;
        $arr['modificada_em'] = $this->modificadaEm?->format('Y-m-d H:i:s');
        $arr['modificada_por'] = $this->modificadaPor;

        return $arr;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getVisitaId(): int
    {
        return $this->visitaId;
    }

    /**
     * @param int $visitaId
     */
    public function setVisitaId(int $visitaId): void
    {
        $this->visitaId = $visitaId;
    }

    /**
     * @return string
     */
    public function getObservacao(): string
    {
        return $this->observacao;
    }

    /**
     * @param string $observacao
     */
    public function setObservacao(string $observacao): void
    {
        $this->observacao = $observacao;
    }

    /**
     * @return DateTime
     */
    public function getAdicionadaEm(): DateTime
    {
        return $this->adicionadaEm;
    }

    /**
     * @param DateTime $adicionadaEm
     */
    public function setAdicionadaEm(DateTime $adicionadaEm): void
    {
        $this->adicionadaEm = $adicionadaEm;
    }

    /**
     * @return int
     */
    public function getAdicionadaPor(): int
    {
        return $this->adicionadaPor;
    }

    /**
     * @param int $adicionadaPor
     */
    public function setAdicionadaPor(int $adicionadaPor): void
    {
        $this->adicionadaPor = $adicionadaPor;
    }

    /**
     * @return DateTime|null
     */
    public function getModificadaEm(): ?DateTime
    {
        return $this->modificadaEm;
    }

    /**
     * @param DateTime|null $modificadaEm
     */
    public function setModificadaEm(?DateTime $modificadaEm): void
    {
        $this->modificadaEm = $modificadaEm;
    }

    /**
     * @return int|null
     */
    public function getModificadaPor(): ?int
    {
        return $this->modificadaPor;
    }

    /**
     * @param int|null $modificadaPor
     */
    public function setModificadaPor(?int $modificadaPor): void
    {
        $this->modificadaPor = $modificadaPor;
    }


}
