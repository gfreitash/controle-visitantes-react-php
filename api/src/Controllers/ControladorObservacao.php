<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioObservacao;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Models\Observacao;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\RespostaJson;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorObservacao extends ControladorRest
{

    public function __construct(
        private readonly RepositorioObservacao $repositorioObservacao,
        private readonly RepositorioVisita $repositorioVisita,
        private readonly RepositorioUsuario $repositorioUsuario
    ) {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $tipoGet = explode('/', $request->getUri()->getPath())[2];
        $queries = $request->getQueryParams();
        if ($tipoGet === "visita") {
            return $this->obterObservacoesPorVisita($queries);
        } elseif ($tipoGet === "observacao") {
            return $this->obterObservacao($queries);
        } else {
            return new Response(400, [], json_encode(['error' => 'Tipo de busca inválido']));
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $dados = $request->getParsedBody();

        if (empty($dados['idVisita']) || !is_numeric($dados['idVisita'])) {
            return new Response(400, [], json_encode(['error' => 'ID da visita não informado ou inválido']));
        } elseif (empty($dados['observacao'])) {
            return new Response(400, [], json_encode(['error' => 'Observação não informada']));
        } elseif (empty($dados['idUsuario']) || !is_numeric($dados['idUsuario'])) {
            return new Response(400, [], json_encode(['error' => 'ID do usuário não informado ou inválido']));
        }

        $observacao = new Observacao(
            (int) $dados['idVisita'],
            $dados['observacao'],
            new \DateTime(),
            (int) $dados['idUsuario']
        );

        $resultado = $this->repositorioObservacao->criar($observacao);
        if (!$resultado) {
            return new RespostaJson(500, json_encode(['error' => 'Erro ao criar observação']));
        }

        $observacao = $this->repositorioObservacao->buscarPorId($resultado);
        return new Response(201, [], json_encode($observacao));
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        global $_PUT;
        $dados = $_PUT;

        if (empty($dados['id']) || !is_numeric($dados['id'])) {
            return new Response(400, [], json_encode(['error' => 'ID da observação não informado ou inválido']));
        } elseif (empty($dados['observacao'])) {
            return new Response(400, [], json_encode(['error' => 'Observação não informada']));
        } elseif (empty($dados['idUsuario']) || !is_numeric($dados['idUsuario'])) {
            return new Response(400, [], json_encode(['error' => 'ID do usuário não informado ou inválido']));
        }

        $observacao = $this->repositorioObservacao->buscarPorId((int) $dados['id']);
        if (!$observacao) {
            return new Response(404, [], json_encode(['error' => 'Observação não encontrada']));
        }

        $observacao->setObservacao($dados['observacao']);
        $observacao->setModificadaEm(new \DateTime());
        $observacao->setModificadaPor((int) $dados['idUsuario']);

        $resultado = $this->repositorioObservacao->atualizar($observacao);
        if (!$resultado) {
            return new RespostaJson(500, json_encode(['error' => 'Erro ao atualizar observação']));
        } else {
            return new RespostaJson(200, json_encode($observacao));
        }
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_405;
    }

    private function obterObservacoesPorVisita(array $queries): ResponseInterface
    {
        if (empty($queries['id'])) {
            return new Response(400, [], json_encode(['error' => 'ID da visita não informado']));
        } elseif (!is_numeric($queries['id'])) {
            return new Response(400, [], json_encode(['error' => 'ID da visita inválido']));
        }

        $visita = $this->repositorioVisita->buscarPorId($queries['id']);
        if (empty($visita)) {
            return new Response(404, [], json_encode(['error' => 'Visita não encontrada']));
        }

        $ordenar = $queries['ordenar'] ?? 'adicionada_em';
        $ordem = $queries['ordem'] ?? 'DESC';
        $ordenacao = [$ordenar => $ordem];
        $dataInicio = $queries['dataInicio'] ? Utils::tentarCriarDateTime($queries['dataInicio']) : null;
        $dataFim = $queries['dataFim'] ? Utils::tentarCriarDateTime($queries['dataFim']) : null;
        $parametro = new ParametroBusca($ordenacao, dataInicio: $dataInicio, dataFim: $dataFim);

        $observacoes = $this->repositorioObservacao->buscarTodasDeVisita($visita, $parametro);

        foreach ($observacoes as $observacao) {
            if ($observacao->adicionada_por) {
                $observacao->adicionada_por = $this->repositorioUsuario
                    ->buscarPorId($observacao->adicionada_por)
                    ->paraArray();
            }
            if ($observacao->modificada_por) {
                $observacao->modificada_por = $this->repositorioUsuario
                    ->buscarPorId($observacao->modificada_por)
                    ->paraArray();
            }
        }
        $total = count($observacoes);

        $conteudoResposta = [
            'quantidadeTotal' => $total,
            'dados' => $observacoes
        ];

        return new RespostaJson(200, json_encode($conteudoResposta));
    }

    private function obterObservacao(array $queries): ResponseInterface
    {
        if (empty($queries['id'])) {
            return new Response(400, [], json_encode(['error' => 'ID da observação não informado']));
        } elseif (!is_numeric($queries['id'])) {
            return new Response(400, [], json_encode(['error' => 'ID da observação inválido']));
        }

        $observacao = $this->repositorioObservacao->buscarPorId($queries['id']);
        if (empty($observacao)) {
            return new Response(404, [], json_encode(['error' => 'Observação não encontrada']));
        }

        return new RespostaJson(200, json_encode($observacao));
    }
}
