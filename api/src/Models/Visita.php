<?php

namespace App\Visitantes\Models;

use DateTime;
use App\Visitantes\Interfaces\Entidade;
use App\Visitantes\Helpers\Utils;

class Visita extends Entidade
{
    private ?int $id;
    private DadosVisita $dados_visita;
    private DateTime $data_visita;
    private int $cadastrada_por;
    private ?DateTime $modificada_em;
    private ?int $modificada_por;
    private ?DateTime $finalizada_em;
    private ?int $finalizada_por;
    private string $formato_data;

    public const STATUS = ["FECHADA", "ABERTA"];

    /**
     * @param DadosVisita $dados_visita
     * @param DateTime $data_visita
     * @param int $cadastrada_por
     */
    public function __construct(DadosVisita $dados_visita, DateTime $data_visita, int $cadastrada_por)
    {
        $this->dados_visita = $dados_visita;
        $this->data_visita = $data_visita;
        $this->cadastrada_por = $cadastrada_por;

        $this->id = null;
        $this->modificada_em = null;
        $this->modificada_por = null;
        $this->finalizada_em = null;
        $this->finalizada_por = null;
        $this->formato_data = Utils::FORMATOS_DATA['datetime'];
    }


    public function paraArray(): array
    {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['cadastrada_por'] = $this->cadastrada_por;
        $arr['modificada_por'] = $this->modificada_por;
        $arr['finalizada_por'] = $this->finalizada_por;

        $arr['data_visita'] = $this->data_visita->format($this->formato_data);
        $arr['modificada_em'] = $this->modificada_em?->format($this->formato_data);
        $arr['finalizada_em'] = $this->finalizada_em?->format($this->formato_data);

        return $this->dados_visita->paraArray() + $arr;
    }

    /**
     * @return ?int
     */
    public function getId(): ?int
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
     * @return DateTime
     */
    public function getDataVisita(): DateTime
    {
        return $this->data_visita;
    }

    public function setDataVisita(DateTime $data_visita): void
    {
        $this->data_visita = $data_visita;
    }

    /**
     * @return int
     */
    public function getCadastradaPor(): int
    {
        return $this->cadastrada_por;
    }

    /**
     * @param int $cadastrada_por
     */
    public function setCadastradaPor(int $cadastrada_por): void
    {
        $this->cadastrada_por = $cadastrada_por;
    }

    /**
     * @return DateTime|null
     */
    public function getModificadaEm(): ?DateTime
    {
        return $this->modificada_em;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $modificada_em
     */
    public function setModificadaEm($modificada_em): void
    {
        if ($modificada_em instanceof DateTime) {
            $this->modificada_em = $modificada_em;
        } else {
            $this->modificada_em = null;
        }
    }

    /**
     * @return int|null
     */
    public function getModificadaPor(): ?int
    {
        return $this->modificada_por;
    }

    /**
     * @param int|null $modificada_por
     */
    public function setModificadaPor(?int $modificada_por): void
    {
        $this->modificada_por = $modificada_por;
    }

    /**
     * @return DateTime|null
     */
    public function getFinalizadaEm(): ?DateTime
    {
        return $this->finalizada_em;
    }

    /**
     * Espera um DateTime, qualquer outro valor fará a variável ser definida para nulo
     * @param $finalizada_em
     */
    public function setFinalizadaEm($finalizada_em): void
    {
        if ($finalizada_em instanceof DateTime) {
            $this->finalizada_em = $finalizada_em;
        } else {
            $this->finalizada_em = null;
        }
    }

    /**
     * @return int|null
     */
    public function getFinalizadaPor(): ?int
    {
        return $this->finalizada_por;
    }

    /**
     * @param int|null $finalizada_por
     */
    public function setFinalizadaPor(?int $finalizada_por): void
    {
        $this->finalizada_por = $finalizada_por;
    }

    /**
     * @return string
     */
    public function getFormatoData(): string
    {
        return $this->formato_data;
    }

    /**
     * @param string $formato_data
     * @return bool
     */
    public function setFormatoData(string $formato_data): bool
    {
        if (in_array($formato_data, Utils::FORMATOS_DATA, true)) {
            $this->formato_data = $formato_data;
            return true;
        }
        return false;
    }
}
