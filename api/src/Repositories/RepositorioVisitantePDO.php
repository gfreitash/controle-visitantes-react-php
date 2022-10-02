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
    private \PDO $conexao;
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
        $this->conexao = Conexao::criarConexao();
    }

    public function adicionarVisitante($visitante): bool|int
    {
        trigger_error(print_r($visitante, true));
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

    public function alterarVisitante($visitante, $cpfAntigo=false): bool
    {
        $arr = $visitante->paraArray();
        if ($cpfAntigo) {
            $cpf = DadosVisitante::validarCPF($cpfAntigo);
        } else {
            $cpf = $arr['cpf'];
        }

        $this->buscarPorCpf($cpf);
        $this->atribuirPropriedades($arr);
        $this->cpf = $cpfAntigo ? $arr['cpf'] : $cpf;

        return $this->save();
    }

    public function removerVisitantePorCPF(string $cpf): bool
    {
        $this->buscarPorCpf($cpf);
        return $this->destroy();
    }

    public function buscarTodosVisitantes($ordenarPor=false, int $limit=0, int $offset=0): array
    {
        $this->find();
        if ($ordenarPor) {
            $this->order($this->ordenarPor($ordenarPor));
        }
        if ($limit) {
            $this->limit($limit);
        }
        if ($limit && $offset) {
            $this->offset($offset);
        }

        return $this->fetch(true);
    }

    public function buscarVisitantesComo($termo, $ordenar_por=false, int $limit=0, int $offset=0): array
    {
        $where = "cpf LIKE :cpf OR nome like :nome";
        $termo .= "%";
        $this->find($where, "cpf=$termo&nome=$termo");
        if ($ordenar_por) {
            $this->order($this->ordenarPor($ordenar_por));
        }
        if ($limit) {
            $this->limit($limit);
        }
        if ($limit && $offset) {
            $this->offset($offset);
        }

        return $this->fetch(true);
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

    public function obterTotalVisitantes(): int
    {
        $query = "SELECT COUNT(*) FROM tb_visitante";
        $stmt = $this->conexao->query($query);

        return (int) $stmt->fetch();
    }
}
