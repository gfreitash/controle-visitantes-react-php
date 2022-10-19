<?php

namespace App\Visitantes\Models;

use DateTime;
use App\Visitantes\Interfaces\Entidade;
use App\Visitantes\Helpers\Utils;

class Visitante extends Entidade
{
    private ?int $id;
    private DadosVisitante $dadosVisitante;
    private ?DateTime $cadastrado_em;
    private ?int $cadastrado_por;
    private ?int $modificado_por;
    private ?DateTime $modificado_em;
    private string $formatoData;

    public function __construct(DadosVisitante $dadosVisitante)
    {
        $this->dadosVisitante = $dadosVisitante;

        $this->id = null;
        $this->cadastrado_em = null;
        $this->cadastrado_por = null;
        $this->modificado_em = null;
        $this->modificado_por = null;
        $this->formatoData = Utils::FORMATOS_DATA['datetime'];
    }

    public function paraArray(): array
    {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['cadastrado_por'] = $this->cadastrado_por;
        $arr['modificado_por'] = $this->modificado_por;
        $arr['cadastrado_em'] = $this->cadastrado_em?->format($this->formatoData);
        $arr['modificado_em'] = $this->modificado_em?->format($this->formatoData);

        return $arr + $this->dadosVisitante->paraArray();
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
     * @return DadosVisitante
     */
    public function getDadosVisitante(): DadosVisitante
    {
        return $this->dadosVisitante;
    }

    /**
     * @param DadosVisitante $dadosVisitante
     */
    public function setDadosVisitante(DadosVisitante $dadosVisitante): void
    {
        $this->dadosVisitante = $dadosVisitante;
    }

    /**
     * @return DateTime|null
     */
    public function getCadastradoEm(): ?DateTime
    {
        return $this->cadastrado_em;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $cadastrado_em
     */
    public function setCadastradoEm($cadastrado_em): void
    {
        if ($cadastrado_em instanceof DateTime) {
            $this->cadastrado_em = $cadastrado_em;
        } else {
            $this->cadastrado_em = null;
        }
    }

    /**
     * @return int|null
     */
    public function getCadastradoPor(): ?int
    {
        return $this->cadastrado_por;
    }

    /**
     * @param int|null $cadastrado_por
     */
    public function setCadastradoPor(?int $cadastrado_por): void
    {
        if ($cadastrado_por === 0) {
            $this->cadastrado_por = null;
        } else {
            $this->cadastrado_por = $cadastrado_por;
        }
    }

    /**
     * @return int|null
     */
    public function getModificadoPor(): ?int
    {
        return $this->modificado_por;
    }

    /**
     * @param int|null $modificado_por
     */
    public function setModificadoPor(?int $modificado_por): void
    {
        if ($modificado_por === 0) {
            $this->modificado_por = null;
        } else {
            $this->modificado_por = $modificado_por;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getModificadoEm(): ?DateTime
    {
        return $this->modificado_em;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $modificado_em
     */
    public function setModificadoEm($modificado_em): void
    {
        if ($modificado_em instanceof DateTime) {
            $this->modificado_em = $modificado_em;
        } else {
            $this->modificado_em = null;
        }
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
