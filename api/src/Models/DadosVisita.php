<?php

namespace App\Visitantes\Models;

use App\Visitantes\Interfaces\Entidade;

class DadosVisita extends Entidade
{
    private int $visitante_id;
    private string $sala_visita;
    private ?string $motivo_visita;
    private int $foi_liberado;

    public function __construct(int $visitante_id, string $sala_visita, int $foi_liberado)
    {
        $this->visitante_id = $visitante_id;
        $this->sala_visita = $sala_visita;
        $this->foi_liberado = $foi_liberado;
        $this->motivo_visita = null;
    }

    public function paraArray(): array
    {
        $arr = array();
        $arr['visitante_id'] = $this->visitante_id;
        $arr['sala_visita'] = $this->sala_visita;
        $arr['motivo_visita'] = $this->motivo_visita;
        $arr['foi_liberado'] = $this->foi_liberado;

        return $arr;
    }

    /**
     * @return int
     */
    public function getVisitanteId(): int
    {
        return $this->visitante_id;
    }

    /**
     * @param int $visitante_id
     */
    public function setVisitanteId(int $visitante_id): void
    {
        $this->visitante_id = $visitante_id;
    }

    /**
     * @return string
     */
    public function getSalaVisita(): string
    {
        return $this->sala_visita;
    }

    /**
     * @param string $sala_visita
     */
    public function setSalaVisita(string $sala_visita): void
    {
        $this->sala_visita = $sala_visita;
    }

    /**
     * @return string|null
     */
    public function getMotivoVisita(): ?string
    {
        return $this->motivo_visita;
    }

    /**
     * @param string|null $motivo_visita
     */
    public function setMotivoVisita(?string $motivo_visita): void
    {
        $this->motivo_visita = $motivo_visita;
    }

    /**
     * @return int
     */
    public function getFoiLiberado(): int
    {
        return $this->foi_liberado;
    }

    /**
     * @param int $foi_liberado
     */
    public function setFoiLiberado(int $foi_liberado): void
    {
        $this->foi_liberado = $foi_liberado;
    }


}
