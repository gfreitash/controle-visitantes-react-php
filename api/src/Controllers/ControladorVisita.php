<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisita;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\RespostaJson;
use App\Visitantes\Models\Visita;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorVisita extends ControladorRest
{
    private array $ERROS = [
        0 => 'Visitante não encontrado',
        1 => 'ID do usuário não informado',
        2 => 'Dados incompletos ou inválidos',
        3 => 'CPF inválido',
        4 => 'Ordem ou tipo de ordenação inválida'
    ];

    public function __construct(
        private readonly RepositorioVisita $repositorioVisita,
        private readonly RepositorioVisitante $repositorioVisitante
    )
    {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $dados = $request->getParsedBody();

        if (empty($dados['idUsuario'])) {
            return new RespostaJson(403, json_encode(['error' => $this->ERROS[1]]));
        }

        if (
            empty($dados['cpf'])
            || DadosVisitante::validarCPF($dados['cpf']) === false
            || empty($dados['salaVisita'])
            || !is_numeric($dados['foiLiberado'])
            || ((int) $dados['foiLiberado']) < 0
            || ((int) $dados['foiLiberado']) > 1
        ) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        $idVisitante = $this->repositorioVisitante->buscarPorCpf($dados['cpf'])?->getId();
        if (!$idVisitante) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[0]]));
        }

        $dadosVisita = new DadosVisita(
            $idVisitante,
            $dados['salaVisita'],
            $dados['foiLiberado']
        );
        $dadosVisita->setMotivoVisita($dados['motivoVisita'] ?? null);

        $visita = new Visita($dadosVisita, new \DateTime(), $dados['idUsuario']);
        if ((int) $dados['foiLiberado'] === 0) {
            $visita->setFinalizadaEm(new \DateTime());
            $visita->setFinalizadaPor($dados['idUsuario']);
        }

        $resultado = $this->repositorioVisita->adicionarVisita($visita);
        if (!$resultado) {
            return $this->_500;
        }

        return new RespostaJson(201, json_encode($resultado));
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_405;
    }
}
