<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisita;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\RespostaJson;
use App\Visitantes\Models\Visita;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Repositories\RepositorioVisitaPDO;
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
        $queries=$request->getQueryParams();

        if (!empty($queries['id'])) {
            return $this->obterVisitaPorId($queries['id']);
        } elseif (!empty($queries['idVisitante'])) {
            return $this->obterVisitasPorIdVisitante($queries['idVisitante']);
        } else {
            return $this->obterTodasVisitas(explode('/', $request->getUri()->getPath()), $queries);
        }
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
        $params = $request->getQueryParams();
        if (empty($params['id']) || !is_numeric($params['id']) || empty($params['idUsuario'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        $visita = $this->repositorioVisita->buscarPorId($params['id']);
        if (!$visita) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[0]]));
        }

        $visita->setFinalizadaPor((int) $params['idUsuario']);
        $visita->setFinalizadaEm(new \DateTime());

        $resultado = $this->repositorioVisita->alterarVisita($visita);

        if (!$resultado) {
            return new RespostaJson(500, json_encode(['error' => 'Erro interno']));
        } else {
            return new RespostaJson(200, json_encode($visita));
        }
    }

    private function obterVisitaPorId(string $id): ResponseInterface
    {
        $visita = $this->repositorioVisita->buscarPorId($id);
        if (!$visita) {
            return new RespostaJson(404, json_encode(['error' => 'Visita não encontrada']));
        } else {
            $visita->setFormatoData(Utils::FORMATOS_DATA['web']);
        }

        return new RespostaJson(200, json_encode($visita));
    }

    private function obterVisitasPorIdVisitante(Visitante $visitante): ResponseInterface
    {
        $visitas = $this->repositorioVisita->obterTodasVisitasDeVisitante($visitante);
        if (!$visitas) {
            return new RespostaJson(404, json_encode(['error' => 'Nenhuma visita encontrada']));
        }

        return new RespostaJson(200, json_encode($visitas));
    }

    private function obterTodasVisitas(array $params, array $queries): ResponseInterface
    {
        if (!$this->parametrosValidos($params)) {
            return new RespostaJson(404, json_encode(['erro' => 'Rota não encontrada']));
        }

        $status = $params[2];
        $ordenarPor = $queries['ordenar'] ?? 'data_visita';
        $ordem = $queries['ordem'] ?? 'DESC';
        $limite = $queries['limite'] ?? null;
        $pagina = $queries['pagina'] ?? 1;
        $buscarPor = RepositorioVisitaPDO::BUSCAR_POR;
        $buscarPorJoin = RepositorioVisitaPDO::BUSCAR_POR_JOIN;

        $offset = ($pagina - 1) * $limite;
        if (array_key_exists($ordenarPor, $buscarPor) && in_array(strtoupper($ordem), ['ASC', 'DESC'])) {
            $buscarPor = [$buscarPor[$ordenarPor] => $ordem] + $buscarPorJoin;
        } else {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[4]]));
        }

        if (strtoupper($status) === "ABERTAS") {
            $status = Visita::STATUS[1];
        } elseif (strtoupper($status) === "FECHADAS") {
            $status = Visita::STATUS[0];
        } elseif (strtoupper($status) === "TODAS" || strtoupper($status) === "") {
            $status = null;
        } else {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        $quantidadeVisitas = $this->repositorioVisita->obterTotalVisitas($status);
        if ($limite) {
            $resultado = $status
                ? $this->repositorioVisita->obterTodasVisitas($status, $buscarPor, $limite, $offset)
                : $this->repositorioVisita->obterTodasVisitas("", $buscarPor, $limite, $offset);
            $quantidadePagina = ceil($quantidadeVisitas / $limite);
        } else {
            $resultado = $status
                ? $this->repositorioVisita->obterTodasVisitas($status, $buscarPor)
                : $this->repositorioVisita->obterTodasVisitas("", $buscarPor);
            $quantidadePagina = 1;
        }

        $conteudoResposta = [
            'quantidadeTotal' => $quantidadeVisitas,
            'quantidadePaginas' => $quantidadePagina,
            'paginaAtual' => $pagina,
            'dados' => $resultado
        ];

        return new RespostaJson(200, json_encode($conteudoResposta));
    }

    private function parametrosValidos(array $params): bool
    {
        $paramsValidos = ['abertas', 'fechadas', 'todas', ''];
        return count($params) === 3 && in_array(strtolower($params[2]), $paramsValidos);
    }
}