<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Interfaces\JoinableDataLayer;
use App\Visitantes\Models\Conexao;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\ParametroBusca;
use CoffeeCode\DataLayer\DataLayer;
use DateTime;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Helpers\Utils;

class RepositorioVisitantePDO extends JoinableDataLayer implements RepositorioVisitante
{
    public const BUSCAR_POR = [
        'nome' => 'ASC',
        'cpf' => 'ASC',
        'modificado_em' => 'DESC',
        'cadastrado_em' => 'DESC',
        'data_nascimento' => 'ASC'
    ];

    public function __construct()
    {
        parent::__construct("tb_visitante", ["cpf", "nome"], "id", false);
    }

    public function criar($visitante): bool|int
    {
        $arr = $visitante->paraArray();
        unset($arr['modificado_em'], $arr['modificado_por']);

        $this->atribuirPropriedades($arr);
        $this->cpf = DadosVisitante::validarCPF($arr['cpf']);
        if ($this->save()) {
            return $this->buscarPorCpf($this->cpf)?->getId() ?? false;
        }
        return false;
    }

    public function buscarPorCpf(string $cpf): bool|Visitante
    {
        $cpf = DadosVisitante::validarCPF($cpf);
        if (!$cpf) {
            return false;
        }

        $vs = $this->find("cpf = :cpf", "cpf=$cpf")->fetch();
        if ($vs) {
            return $this->criarVisitante($vs);
        }

        return false;
    }

    public function buscarPorId($id): bool|Visitante
    {
        $vs = $this->findById($id);
        if ($vs) {
            $this->id = $vs->id;
            return $this->criarVisitante($vs);
        }
        return false;
    }

    public function atualizar($visitante): bool
    {
        $arr = $visitante->paraArray();
        $vs = $this->findById((int) $arr['id']);
        if (!$vs) {
            $vs = $this->find("cpf = :cpf", "cpf={$arr['cpf']}");
        }
        if (!$vs) {
            return false;
        }

        $vs->atribuirPropriedades($arr);
        return $vs->save();
    }

    public function removerPorCpf(string $cpf): bool
    {
        $this->buscarPorCpf($cpf);
        return $this->destroy();
    }

    public function buscarTodos(ParametroBusca $parametros = null): array
    {
        [$where, $params] = $this->definirDetalhesBusca($parametros);
        $this->find($where, $params);

        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function buscarComo($termo, ParametroBusca $parametros = null): array
    {
        $where = "";
        $params = "";

        if (DateTime::createFromFormat('d/m/Y', $termo)) {
            $termo = Utils::formatarData($termo, Utils::FORMATOS_DATA['date_local'], Utils::FORMATOS_DATA['date']);
            $where .= "cadastrado_em LIKE :termo";
            $params .= "termo=$termo%";
        } elseif (strlen($termo) > 0) {
            $termo .= "%";
            $where .= "cpf LIKE :cpf OR nome like :nome";
            $params .= "cpf=$termo&nome=$termo";
        }

        [$where, $params] = $this->definirDetalhesBusca($parametros, $where, $params);
        $this->find($where, $params);

        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function obterIdVisitante(string $cpf): int
    {
        $this->buscarPorCpf($cpf);
        return $this->id;
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
            $where = Utils::concatWhere($where, "cadastrado_em >= :dataInicio");
            $params .=  strlen($params) > 0 ? "&" : "";
            $params .= "dataInicio={$parametros->dataInicio->format(Utils::FORMATOS_DATA['date'])}";
        }
        if ($parametros?->dataFim) {
            $where = Utils::concatWhere($where, "cadastrado_em <= :dataFim");
            $params .=  strlen($params) > 0 ? "&" : "";
            $params .= "dataFim={$parametros->dataFim->format(Utils::FORMATOS_DATA['date'])}";
        }

        return [$where, $params];
    }

    private function criarVisitante(DataLayer|array $vs): Visitante
    {
        if (gettype($vs) === 'array') {
            $vs = (object) $vs;
        }

        $dadosVisitante = new DadosVisitante($vs->cpf, $vs->nome);
        $dadosVisitante->setIdentidade($vs->identidade);
        $dadosVisitante->setExpedidor($vs->expedidor);
        $dadosVisitante->setFoto($vs->foto);
        $dadosVisitante->setDataNascimento(
            DateTime::createFromFormat(Utils::FORMATOS_DATA['date'], $vs->data_nascimento)
        );

        $visitante = new Visitante($dadosVisitante);
        $visitante->setId($vs->id);
        $visitante->setCadastradoPor($vs->cadastrado_por);
        $visitante->setModificadoPor($vs->modificado_por);

        $visitante->setCadastradoEm(
            DateTime::createFromFormat(Utils::FORMATOS_DATA['datetime'], $vs->cadastrado_em)
        );
        $visitante->setModificadoEm(
            DateTime::createFromFormat(Utils::FORMATOS_DATA['datetime'], $vs->modificado_em)
        );

        return $visitante;
    }

    public static function obterRepositorioVisitante(): RepositorioVisitante
    {
        return new RepositorioVisitantePDO();
    }

    public function obterTotal(string $como = "", ParametroBusca $parametros = null): int
    {
        $conexao = Conexao::criarConexao();
        $where = "";
        $params = [];

        if ($como) {
            if ($data = DateTime::createFromFormat('d/m/Y', $como)) {
                if (strlen($where) > 0) {
                    $where .= " AND ";
                }

                $como = $data->format(Utils::FORMATOS_DATA['date']);
                $where .= "cadastrado_em LIKE :como";
                $params['como'] = $como."%";
            } else {
                if (strlen($where) > 0) {
                    $where .= " AND ";
                }
                $como .= "%";
                $where = "cpf LIKE :cpf OR nome LIKE :nome";
                $params['cpf'] = $como;
                $params['nome'] = $como;
            }
        }

        if ($parametros?->dataInicio) {
            if (strlen($where) > 0) {
                $where .= " AND ";
            }
            $where .= "cadastrado_em >= :dataInicio";
            $params['dataInicio'] = $parametros->dataInicio->format(Utils::FORMATOS_DATA['date']);
        }
        if ($parametros?->dataFim) {
            if (strlen($where) > 0) {
                $where .= " AND ";
            }
            $where .= "cadastrado_em <= :dataFim";
            $params['dataFim'] = $parametros->dataFim->format(Utils::FORMATOS_DATA['date']);
        }

        $query = "SELECT COUNT(*) FROM tb_visitante";
        if (strlen($where) > 0) {
            $query .= " WHERE $where";
        }
        $stmt = $conexao->prepare($query);

        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return (int) $stmt->fetch()["COUNT(*)"];
    }
}
