<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisita;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\ParametroBusca;
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
        4 => 'Ordem ou tipo de ordenação inválida',
        5 => 'Visita não encontrada',
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

        $resultado = $this->repositorioVisita->criar($visita);
        if (!$resultado) {
            return $this->_500;
        }

        return new RespostaJson(201, json_encode($resultado));
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        global $_PUT;
        $dados = $_PUT;

        if (empty($dados['idUsuario'])) {
            return new RespostaJson(403, json_encode(['error' => $this->ERROS[1]]));
        } elseif (empty($dados['id']) || empty($dados['salaVisita'])
            || ((int) $dados['foiLiberado']) < 0 || ((int) $dados['foiLiberado']) > 1) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        $visita = $this->repositorioVisita->buscarPorId($dados['id']);
        if (!$visita) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[5]]));
        }

        $visita->getDadosVisita()->setSalaVisita($dados['salaVisita']);
        $visita->getDadosVisita()->setFoiLiberado($dados['foiLiberado']);
        $visita->getDadosVisita()->setMotivoVisita($dados['motivoVisita'] ?? null);
        $visita->setModificadaEm(new \DateTime());
        $visita->setModificadaPor($dados['idUsuario']);

        if ((int) $dados['foiLiberado'] === 0) {
            $visita->setFinalizadaEm(new \DateTime());
            $visita->setFinalizadaPor($dados['idUsuario']);
        }

        $resultado = $this->repositorioVisita->atualizar($visita);

        if (!$resultado) {
            return $this->_500;
        }

        return new RespostaJson(200, json_encode($visita));
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (empty($params['id']) || !is_numeric($params['id']) || empty($params['idUsuario'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        $visita = $this->repositorioVisita->buscarPorId($params['id']);
        if (!$visita) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[5]]));
        }

        $visita->setFinalizadaPor((int) $params['idUsuario']);
        $visita->setFinalizadaEm(new \DateTime());

        $resultado = $this->repositorioVisita->atualizar($visita);

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
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[5]]));
        } else {
            $visita->setFormatoData(Utils::FORMATOS_DATA['web']);
        }

        return new RespostaJson(200, json_encode($visita));
    }

    private function obterVisitasPorIdVisitante(Visitante $visitante): ResponseInterface
    {
        $visitas = $this->repositorioVisita->buscarTodasDeVisitante($visitante);
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
        $dataInicio = key_exists('dataInicio', $queries) ? Utils::tentarCriarDateTime($queries['dataInicio']) : null;
        $dataFim = key_exists('dataFim', $queries) ? Utils::tentarCriarDateTime($queries['dataFim']) : null;
        $ordenarPor = $queries['ordenar'] ?? 'data_visita';
        $ordem = $queries['ordem'] ?? 'DESC';
        $limite = $queries['limite'] ?? 0;
        $pagina = $queries['pagina'] ?? 1;
        $buscarPor = RepositorioVisitaPDO::BUSCAR_POR;
        $buscarPorJoin = RepositorioVisitaPDO::BUSCAR_POR_JOIN;

        $offset = $limite ? ($pagina - 1) * $limite : 0;
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
            $status = "";
        } else {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }


        $quantidadeVisitas = $this->repositorioVisita->obterTotal(
            $status,
            new ParametroBusca(dataInicio: $dataInicio, dataFim: $dataFim)
        );
        $resultado = $this->repositorioVisita->buscarTodas(
            $status,
            new ParametroBusca($buscarPor, $limite, $offset, $dataInicio, $dataFim)
        );
        $quantidadePagina = $limite ? ceil($quantidadeVisitas / $limite) : 1;


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
