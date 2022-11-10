<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\RespostaJson;
use App\Visitantes\Models\Visita;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorDashboard extends ControladorRest
{

    public function __construct(
        private readonly RepositorioVisitante $repositorioVisitante,
        private readonly RepositorioVisita $repositorioVisita
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $formatoData = 'd/m/Y';
        $dados = [
            'visitas' => [],
            'visitantes' => [],
        ];

        try {
            //DateTime do dia atual
            $hoje = new DateTime();
            $dados['hoje'] = $hoje->format($formatoData);
            //DateTime dessa semana
            $dia = new DateTime($hoje->format('Y-m-d'));
            $semana = ($dia->sub(new \DateInterval("P" . $dia->format('w') . "D")));
            $dados['semana'] = $semana->format($formatoData);
            //DateTime desse mês
            $mes = DateTime::createFromFormat('Y-m-d', date('Y-m-01'));
            $dados['mes'] = $mes->format($formatoData);
            //DateTime desse ano
            $ano = DateTime::createFromFormat('Y-m-d', date('Y-01-01'));
            $dados['ano'] = $ano->format($formatoData);
        } catch (\Exception $e) {
            return new RespostaJson(500, $e->getMessage());
        }

        //obter visitas em aberto
        $visitasAbertas = $this->repositorioVisita->buscarTodas(Visita::STATUS[1]);
        $dados['visitas']['abertas'] = count($visitasAbertas);

        //obter visitas nesse dia
        $parametros = new ParametroBusca(dataInicio: $hoje);
        $visitasHoje = $this->repositorioVisita->buscarTodas(parametros: $parametros);
        $dados['visitas']['hoje'] = count($visitasHoje);

        //obter visitas nessa semana
        $parametros = new ParametroBusca(dataInicio: $semana);
        $visitasSemana = $this->repositorioVisita->buscarTodas(parametros: $parametros);
        $dados['visitas']['semana'] = count($visitasSemana);

        //obter visitas nesse mês
        $parametros = new ParametroBusca(dataInicio: $mes);
        $visitasMes = $this->repositorioVisita->buscarTodas(parametros: $parametros);
        $dados['visitas']['mes'] = count($visitasMes);

        //obter visitas nesse ano
        $parametros = new ParametroBusca(dataInicio: $ano);
        $visitasAno = $this->repositorioVisita->buscarTodas(parametros: $parametros);
        $dados['visitas']['ano'] = count($visitasAno);

        //obter visitantes ativos nesse dia
        $parametros = new ParametroBusca(dataInicio: $hoje);
        $visitantesHoje = $this->repositorioVisita->buscarVisitantesAtivos(parametros: $parametros);
        $dados['visitantes']['ativos']['hoje'] = count($visitantesHoje);

        //obter visitantes ativos nessa semana
        $parametros = new ParametroBusca(dataInicio: $semana);
        $visitantesSemana = $this->repositorioVisita->buscarVisitantesAtivos(parametros: $parametros);
        $dados['visitantes']['ativos']['semana'] = count($visitantesSemana);

        //obter visitantes ativos nesse mês
        $parametros = new ParametroBusca(dataInicio: $mes);
        $visitantesMes = $this->repositorioVisita->buscarVisitantesAtivos(parametros: $parametros);
        $dados['visitantes']['ativos']['mes'] = count($visitantesMes);

        //obter visitantes ativos nesse ano
        $parametros = new ParametroBusca(dataInicio: $ano);
        $visitantesAno = $this->repositorioVisita->buscarVisitantesAtivos(parametros: $parametros);
        $dados['visitantes']['ativos']['ano'] = count($visitantesAno);

        //obter visitantes cadastrados nesse dia
        $parametros = new ParametroBusca(dataInicio: $hoje);
        $visitantesHoje = $this->repositorioVisitante->buscarTodos(parametros: $parametros);
        $dados['visitantes']['cadastrados']['hoje'] = count($visitantesHoje);

        //obter visitantes cadastrados nessa semana
        $parametros = new ParametroBusca(dataInicio: $semana);
        $visitantesSemana = $this->repositorioVisitante->buscarTodos(parametros: $parametros);
        $dados['visitantes']['cadastrados']['semana'] = count($visitantesSemana);

        //obter visitantes cadastrados nesse mês
        $parametros = new ParametroBusca(dataInicio: $mes);
        $visitantesMes = $this->repositorioVisitante->buscarTodos(parametros: $parametros);
        $dados['visitantes']['cadastrados']['mes'] = count($visitantesMes);

        //obter visitantes cadastrados nesse ano
        $parametros = new ParametroBusca(dataInicio: $ano);
        $visitantesAno = $this->repositorioVisitante->buscarTodos(parametros: $parametros);
        $dados['visitantes']['cadastrados']['ano'] = count($visitantesAno);

        return new RespostaJson(200, json_encode($dados));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_405;
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_405;
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_405;
    }
}
