<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Interfaces\JoinableDataLayer;
use App\Visitantes\Models\Conexao;
use App\Visitantes\Models\DadosVisita;
use App\Visitantes\Models\ParametroBusca;
use CoffeeCode\DataLayer\DataLayer;
use DateTime;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Models\Visita;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Helpers\Utils;

class RepositorioVisitaPDO extends JoinableDataLayer implements RepositorioVisita
{
    public const BUSCAR_POR = [
        'id' => 'id',
        'data_visita' => 'tb_visita.data_visita',
        'nome' => 'tb_visitante.nome',
        'modificada_em' => 'tb_visita.modificada_em',
        'finalizada_em' => 'tb_visita.finalizada_em',
        'cpf' => 'tb_visitante.cpf',
        'sala_visita' => 'tb_visita.sala_visita',
        'motivo_visita' => 'tb_visita.motivo_visita'
    ];

    public const BUSCAR_POR_JOIN = [
        'tb_visita.data_visita' => 'DESC',
        'tb_visitante.nome' => 'ASC',
        'tb_visita.modificada_em' => 'DESC',
        'tb_visita.finalizada_em' => 'DESC',
        'tb_visitante.cpf' => 'ASC',
        'tb_visita.sala_visita' => 'ASC',
        'tb_visita.motivo_visita' => 'ASC'
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

    private function obterVisitante(): RepositorioVisitaPDO
    {
        $dadosVisitante = RepositorioVisitantePDO::obterRepositorioVisitante()
            ->findById($this->visitante_id, "cpf, nome")
            ->data();

        $this->visitante = $dadosVisitante;
        return $this;
    }

    public function criar(Visita $visita): bool|Visita
    {
        $arr = $visita->paraArray();
        unset($arr['modificada_em'], $arr['modificada_por'], $arr['cpf'], $arr['nome']);
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

    public function atualizar(Visita $visita): bool
    {
        $arr = $visita->paraArray();
        unset($arr['cpf'], $arr['nome']);

        /** @var RepositorioVisitaPDO|null $vs */
        $vs = $this->findById($arr['id']);
        if ($vs) {
            $vs->atribuirPropriedades($arr);
            return $vs->save();
        }

        return false;
    }

    public function removerPorId($id): bool
    {
        $this->findById($id);
        return $this->destroy();
    }

    public function buscarTodas(
        string $status = "",
        ParametroBusca $parametros = null
    ): array
    {
        $repVisitante = RepositorioVisitantePDO::obterRepositorioVisitante();
        $entidadeVisita = $this->getEntity();
        $entidadeVisitante = $repVisitante->getEntity();
        $colunas = "$entidadeVisita.*, $entidadeVisitante.nome, $entidadeVisitante.cpf";

        $terms = "";
        if ($status && in_array($status, Visita::STATUS)) {
            if ($status === Visita::STATUS[0]) {
                $terms .= "$entidadeVisita.finalizada_em IS NOT NULL";
            } else {
                $terms .= "$entidadeVisita.finalizada_em IS NULL";
            }
        }

        [$where, $params] = $this->definirDetalhesBusca($parametros, $terms);

        $this->findWithJoin(
            $entidadeVisitante,
            "id",
            "visitante_id",
            $where,
            $params,
            $colunas
        );
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function buscarTodasDeVisitante(
        Visitante $visitante,
        string $status = "",
        ParametroBusca $parametros = null
    ): array
    {
        $repVisitante = RepositorioVisitantePDO::obterRepositorioVisitante();
        $vs = $repVisitante->buscarPorCpf($visitante->getDadosVisitante()->getCpf());
        if (!$vs) {
            return array();
        }
        $visitanteId = $vs->getId();
        $entidadeVisita = $this->getEntity();
        $entidadeVisitante = $repVisitante->getEntity();
        $colunas = "$entidadeVisita.*, $entidadeVisitante.nome, $entidadeVisitante.cpf";

        $where = "$entidadeVisita.visitante_id = :visitante_id";
        $param = "visitante_id=$visitanteId";

        if ($status && in_array($status, Visita::STATUS)) {
            if ($status === Visita::STATUS[0]) {
                $where .= " AND finalizada_em IS NOT NULL";
            } else {
                $where .= " AND finalizada_em IS NULL";
            }
        }

        [$where, $params] = $this->definirDetalhesBusca($parametros, $where, $param);

        $this->findWithJoin(
            $entidadeVisitante,
            "id",
            "visitante_id",
            $where,
            $params,
            $colunas
        );
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function buscarVisitantesAtivos(?ParametroBusca $parametros = null): array
    {
        $repVisitante = RepositorioVisitantePDO::obterRepositorioVisitante();
        $entidadeVisitante = $repVisitante->getEntity();
        $colunas = "$entidadeVisitante.*";

        [$where, $params] = $this->definirDetalhesBusca($parametros);
        $this->group("$entidadeVisitante.id");

        $this->findWithJoin(
            $entidadeVisitante,
            "id",
            "visitante_id",
            $where,
            $params,
            $colunas
        );
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }


    public function obterTotal(?string $status = ""): int
    {
        $conexao = Conexao::criarConexao();
        if ($status) {
            if (in_array($status, Visita::STATUS)) {
                if ($status === Visita::STATUS[0]) {
                    $where = "finalizada_em IS NOT NULL";
                } else {
                    $where = "finalizada_em IS NULL";
                }
            } else {
                return 0;
            }

            $query = "SELECT COUNT(*) FROM tb_visita WHERE $where";
        } else {
            $query = "SELECT COUNT(*) FROM tb_visita";
        }

        $stmt = $conexao->query($query);
        return (int) $stmt->fetch()["COUNT(*)"];
    }

    public static function obterRepositorioVisita(): RepositorioVisita
    {
        return new RepositorioVisitaPDO();
    }


    private function criarVisita(DataLayer|array $vs): Visita
    {
        if ($vs instanceof RepositorioVisitaPDO) {
            $vs->obterVisitante();
        }

        $dados_visita = new DadosVisita(
            $vs->visitante_id,
            $vs->sala_visita,
            $vs->foi_liberado
        );
        $dados_visita->setMotivoVisita($vs->motivo_visita);
        $dados_visita->setCpf($vs->visitante->cpf ?? null);
        $dados_visita->setNome($vs->visitante->nome ?? null);

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

    private function definirDetalhesBusca(?ParametroBusca $parametros = null, $where = "", $params = ""): array
    {
        if ($parametros?->ordenarPor) {
            $this->order(Utils::arrayOrdenacaoParaString($parametros->ordenarPor));
        }
        if ($parametros?->limite) {
            $this->limit($parametros->limite);
        }
        if ($parametros?->limite && $parametros?->offset) {
            $this->offset($parametros->offset);
        }
        if ($parametros?->dataInicio) {
            if (strlen($where > 0)) {
                $where .= " AND ";
            }

            $where .= "data_visita >= :dataInicio";
            $params .=  strlen($params) > 0 ? "&" : "";
            $params .= "dataInicio={$parametros->dataInicio->format(Utils::FORMATOS_DATA['date'])}";
        }
        if ($parametros?->dataFim) {
            if (strlen($where > 0)) {
                $where .= " AND ";
            }

            $where .= "data_visita <= :dataFim";
            $params .=  strlen($params) > 0 ? "&" : "";
            $params .= "dataFim={$parametros->dataFim->format(Utils::FORMATOS_DATA['date'])}";
        }

        return [$where, $params];
    }
}
