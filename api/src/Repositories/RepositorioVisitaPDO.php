<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Models\DadosVisita;
use CoffeeCode\DataLayer\DataLayer;
use DateTime;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Models\Visita;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Helpers\Utils;

class RepositorioVisitaPDO extends DataLayer implements RepositorioVisita
{
    public const BUSCAR_POR = [
        'data_visita' => 'DESC',
        'modificada_em' => 'DESC',
        'finalizada_em' => 'DESC'
    ];

    public function __construct()
    {
        parent::__construct(
            "tb_visita",
            ["visitante_id", "sala_visita", "foi_liberado", "data_visita", "cadastrada_por"],
            "id",
            false
        );
    }

    public function adicionarVisita(Visita $visita): bool|Visita
    {
        $arr = $visita->paraArray();
        unset($arr['modificada_em'], $arr['modificada_por']);
        $this->atribuirPropriedades($arr);

        if ($this->save()) {
            return $this->buscarPorId($this->id);
        }
        return false;
    }

    public function buscarPorId($id): bool|Visita
    {
        $retorno = $this->findById($id);
        if ($retorno) {
            return $this->criarVisita($retorno);
        }
        return false;
    }

    public function alterarVisita(Visita $visita): bool
    {
        $arr = $visita->paraArray();

        /** @var RepositorioVisitaPDO|null $vs */
        $vs = $this->findById($arr['id']);
        if ($vs) {
            $vs->atribuirPropriedades($arr);
            return $vs->save();
        }

        return false;
    }

    public function removerVisitaPorId($id): bool
    {
        $this->findById($id);
        return $this->destroy();
    }

    public function obterTodasVisitas(string $status = "", $ordenarPor = false, int $limite = 0, int $offset = 0): array
    {
        $terms = "";
        if ($status && in_array($status, Visita::STATUS)) {
            if ($status === Visita::STATUS[0]) {
                $terms .= "AND finalizada_em IS NOT NULL";
            } else {
                $terms .= "AND finalizada_em IS NULL";
            }
        }

        if ($ordenarPor && in_array($ordenarPor, self::BUSCAR_POR)) {
            $this->order($this->ordenarPor($ordenarPor));
        }

        if ($limite > 0) {
            $this->limit($limite);
        }

        if ($offset > 0) {
            $this->offset($offset);
        }

        $this->find($terms);
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function obterTodasVisitasDeVisitante(
        Visitante $visitante,
        string $status = "",
        $ordenarPor = false,
        int $limite = 0,
        int $offset = 0
    ): array
    {
        $repVisitante = RepositorioVisitantePDO::obterRepositorioVisitante();
        $vs = $repVisitante->buscarPorCpf($visitante->getDadosVisitante()->getCpf());
        if (!$vs) {
            return array();
        }
        $visitanteId = $vs->getId();
        $where = "visitante_id = :visitante_id";
        $param = "visitante_id=$visitanteId";

        if ($status && in_array($status, Visita::STATUS)) {
            if ($status === Visita::STATUS[0]) {
                $where .= " AND finalizada_em IS NOT NULL";
            } else {
                $where .= " AND finalizada_em IS NULL";
            }
        }

        if ($ordenarPor && in_array($ordenarPor, self::BUSCAR_POR)) {
            $this->order($this->ordenarPor($ordenarPor));
        }

        if ($limite > 0) {
            $this->limit($limite);
        }

        if ($offset > 0) {
            $this->offset($offset);
        }

        $this->find($where, $param);
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public static function obterRepositorioVisita(): RepositorioVisita
    {
        return new RepositorioVisitaPDO();
    }


    private function criarVisita(DataLayer|array $vs): Visita
    {
        $dados_visita = new DadosVisita(
            $vs->visitante_id,
            $vs->sala_visita,
            $vs->foi_liberado
        );
        $dados_visita->setMotivoVisita($vs->motivo_visita);

        $visita = new Visita(
            $dados_visita,
            DateTime::createFromFormat(Utils::FORMATOS_DATA['datetime'], $vs->data_visita),
            $vs->cadastrada_por
        );
        $visita->setId($vs->id);
        $visita->setModificadaPor($vs->modificada_por);
        $visita->setFinalizadaPor($vs->finalizada_por);

        $visita->setModificadaEm(
            DateTime::createFromFormat(Utils::FORMATOS_DATA['datetime'], $vs->modificada_em)
        );
        $visita->setFinalizadaEm(
            DateTime::createFromFormat(Utils::FORMATOS_DATA['datetime'], $vs->finalizada_em)
        );

        return $visita;
    }

    private function ordenarPor(array $arrayAssoc): string
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

    private function atribuirPropriedades(array $propriedades): void
    {
        foreach ($propriedades as $propriedade => $valor) {
            $this->$propriedade = $valor;
        }
    }
}
