<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\JoinableDataLayer;
use App\Visitantes\Interfaces\RepositorioObservacao;
use App\Visitantes\Models\Conexao;
use App\Visitantes\Models\Observacao;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\Visita;
use CoffeeCode\DataLayer\DataLayer;

class RepositorioObservacaoPDO extends JoinableDataLayer implements RepositorioObservacao
{
    public function __construct()
    {
        parent::__construct(
            "tb_observacao",
            ["visita_id", "observacao", "adicionada_em", "adicionada_por"],
            timestamps: false
        );
    }

    public function criar(Observacao $observacao): bool|int
    {
        $arr = $observacao->paraArray();
        unset($arr['id']);

        $this->atribuirPropriedades($arr);
        if ($this->save()) {
            return $this->id;
        }
        return false;
    }

    public function buscarPorId(int $id): bool|Observacao
    {
        $obs = $this->findById($id);
        if ($obs) {
            return $this->criarObservacao($obs);
        }
        return false;
    }

    public function atualizar(Observacao $observacao): bool
    {
        $arr = $observacao->paraArray();

        /** @var JoinableDataLayer|null $obs */
        $obs = $this->findById($arr['id']);
        if ($obs) {
            $obs->atribuirPropriedades($arr);
            return $obs->save();
        }

        return false;
    }

    public function buscarTodasDeVisita(Visita $visita, ParametroBusca $parametros = null): array
    {
        $where = "visita_id = :visita_id";
        $params = "visita_id={$visita->getId()}";

        [$where, $params] = $this->definirDetalhesBusca($parametros, $where, $params);
        $rs = $this->find($where, $params)->fetch(true);

        return $this->count() > 0 ? array_map(fn($obs) => $obs->data(), $rs) : [];
    }

    public function obterTotalDeVisita(Visita $visita, ParametroBusca $parametros = null): int
    {
        $conexao = Conexao::criarConexao();
        $where = "visita_id = :visita_id";
        $params = "visita_id={$visita->getId()}";

        [$where, $params] = $this->definirDetalhesBusca($parametros, $where, $params);
        $rs = $conexao->query("SELECT COUNT(*) AS total FROM tb_observacao WHERE $where", $params)->fetch();

        return (int) $rs['COUNT(*)'];
    }

    public static function obterRepositorioObservacao(): RepositorioObservacao
    {
        return new RepositorioObservacaoPDO();
    }

    private function criarObservacao(DataLayer|array $obs): Observacao
    {
        if (gettype($obs) === 'array') {
            $obs = (object) $obs;
        }

        $observacao = new Observacao(
            $obs->visita_id,
            $obs->observacao,
            Utils::tentarCriarDateTime($obs->adicionada_em),
            $obs->adicionada_por
        );
        $observacao->setId($obs->id);
        $observacao->setModificadaEm(Utils::tentarCriarDateTime($obs->modificada_em));
        $observacao->setModificadaPor($obs->modificada_por);

        return $observacao;
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
            $where = Utils::concatWhere($where, "adicionada_em >= :data_inicio");
            $params = Utils::concatParam($params, "data_inicio={$parametros->dataInicio->format('Y-m-d')}");
        }

        if ($parametros?->dataFim) {
            $where = Utils::concatWhere($where, "adicionada_em <= :data_fim");
            $params = Utils::concatParam($params, "data_fim={$parametros->dataFim->format('Y-m-d')}");
        }

        return [$where, $params];
    }
}
