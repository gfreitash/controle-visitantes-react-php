<?php

namespace App\Visitantes\Repositories;

use App\Visitantes\Models\Conexao;
use App\Visitantes\Models\DadosVisitante;
use CoffeeCode\DataLayer\DataLayer;
use DateTime;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Helpers\Utils;

class RepositorioVisitantePDO extends DataLayer implements RepositorioVisitante
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

    public function adicionarVisitante($visitante): bool|int
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

    public function alterarVisitante($visitante): bool
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

    public function removerVisitantePorCPF(string $cpf): bool
    {
        $this->buscarPorCpf($cpf);
        return $this->destroy();
    }

    public function buscarTodosVisitantes($ordenar_por=false, int $limit=0, int $offset=0): array
    {
        $this->find();
        if ($ordenar_por) {
            $this->order($this->ordenarPor($ordenar_por));
        }
        if ($limit) {
            $this->limit($limit);
        }
        if ($limit && $offset) {
            $this->offset($offset);
        }
        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function buscarVisitantesComo($termo, $ordenarPor=false, int $limit=0, int $offset=0): array
    {
        if (DateTime::createFromFormat('d/m/Y', $termo)) {
            $termo = Utils::formatarData($termo, Utils::FORMATOS_DATA['date_local'], Utils::FORMATOS_DATA['date']);
            $where = "cadastrado_em LIKE :termo";
            $this->find($where, "termo=$termo%");
        } else {
            $where = "cpf LIKE :cpf OR nome like :nome";
            $termo .= "%";
            $this->find($where, "cpf=$termo&nome=$termo");
        }

        if ($ordenarPor) {
            $this->order($this->ordenarPor($ordenarPor));
        }
        if ($limit) {
            $this->limit($limit);
        }
        if ($limit && $offset) {
            $this->offset($offset);
        }

        $resultado = $this->fetch(true);
        return $this->count() ? array_map(fn($rs) => $rs->data(), $resultado) : [];
    }

    public function obterIdVisitante(string $cpf): int
    {
        $this->buscarPorCpf($cpf);
        return $this->id;
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

    private function criarVisitante(DataLayer|array $vs): Visitante
    {
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

    public function obterTotalVisitantes(string $como = ""): int
    {
        $conexao = Conexao::criarConexao();
        if ($como) {
            if (DateTime::createFromFormat('d/m/Y', $como)) {
                $como = Utils::formatarData($como, Utils::FORMATOS_DATA['date_local'], Utils::FORMATOS_DATA['date']);
                $where = "cadastrado_em LIKE :como";
                $query = "SELECT COUNT(*) FROM tb_visitante WHERE $where";
                $stmt = $conexao->prepare($query);
                $stmt->bindValue(":como", $como."%");
            } else {
                $como .= "%";
                $where = "cpf LIKE :cpf OR nome LIKE :nome";
                $query = "SELECT COUNT(*) FROM tb_visitante WHERE $where";
                $stmt = $conexao->prepare($query);
                $stmt->bindValue(":cpf", $como);
                $stmt->bindValue(":nome", $como);
            }
        } else {
            $query = "SELECT COUNT(*) FROM tb_visitante";
            $stmt = $conexao->prepare($query);
        }

        $stmt->execute();
        return (int) $stmt->fetch()["COUNT(*)"];
    }
}
