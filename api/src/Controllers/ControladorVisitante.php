<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Repositories\RepositorioVisitantePDO;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorVisitante extends ControladorRest
{

    public function __construct(private readonly RepositorioVisitante $repositorioVisitante)
    {}

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $cabecalhoResposta = ['Content-Type' => 'application/json'];
        $queries=$request->getQueryParams();
        if (!empty($queries['id'])) {
            $resultado = $this->repositorioVisitante->buscarPorId($queries['id']);
        } elseif (!empty($queries['cpf'])) {
            if (!DadosVisitante::validarCPF($queries['cpf'])) {
                return new Response(
                    400,
                    $cabecalhoResposta,
                    json_encode(['error' => 'CPF inválido']));
            }
            $resultado = $this->repositorioVisitante->buscarPorCpf($queries['cpf']);
        } else {
            $pesquisa = $this->filtrarPesquisa($queries['pesquisa']);
            $ordenarPor = $queries['ordenar'] ?? 'nome';
            $limite = $queries['limite'] ?? null;
            $pagina = $queries['pagina'] ?? 1;
            $ordem = $queries['ordem'] ?? 'ASC';
            $buscarPor = RepositorioVisitantePDO::BUSCAR_POR;

            $offset = ($pagina - 1) * $limite;
            if (array_key_exists($ordenarPor, $buscarPor) && in_array(strtoupper($ordem), ['ASC', 'DESC'])) {
                array_shift($buscarPor);
                $buscarPor = [$ordenarPor => $ordem] + $buscarPor;
            } else {
                return new Response(
                    400,
                    $cabecalhoResposta,
                    json_encode(['error' => 'Ordem ou tipo de ordenação inválida']));
            }

            $quantidadeVisitantes = $this->repositorioVisitante->obterTotalVisitantes($pesquisa ?? '');
            if ($limite) {
                $resultado = $pesquisa
                    ? $this->repositorioVisitante->buscarVisitantesComo($pesquisa, $buscarPor, $limite, $offset)
                    : $this->repositorioVisitante->buscarTodosVisitantes($buscarPor, $limite, $offset);
                $quantidadePaginas = ceil($quantidadeVisitantes / $limite);
            } else {
                $resultado = $pesquisa
                    ? $this->repositorioVisitante->buscarVisitantesComo($pesquisa, $buscarPor)
                    : $this->repositorioVisitante->buscarTodosVisitantes($buscarPor);
                $quantidadePaginas = 1;
            }
            $conteudoResposta = [
                'quantidadeVisitantes' => $quantidadeVisitantes,
                'quantidadePaginas' => $quantidadePaginas,
                'paginaAtual' => $pagina,
                'visitantes' => $resultado
            ];

            return new Response(
                200,
                $cabecalhoResposta,
                json_encode($conteudoResposta));
        }

        if (!$resultado) {
            return new Response(404, $cabecalhoResposta, json_encode(['error' => 'Visitante não encontrado']));
        }

        return new Response(200, $cabecalhoResposta, json_encode($resultado));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $cabecalhoResposta = ['Content-Type' => 'application/json'];
        $dados = $request->getParsedBody();

        if (empty($dados['idUsuario'])) {
            return new Response(
                403,
                $cabecalhoResposta,
                json_encode(['error' => 'ID do usuário não informado']));
        }

        if (empty($dados['nome']) || empty($dados['cpf'])) {
            return new Response(
                400,
                $cabecalhoResposta,
                json_encode(['error' => 'Dados incompletos']));
        }

        if (!DadosVisitante::validarCPF($dados['cpf'])) {
            return new Response(
                400,
                $cabecalhoResposta,
                json_encode(['error' => 'CPF inválido']));
        }

        $upload = $request->getUploadedFiles()['fotoInput'];
        $uploadBinario = $upload->getStream()->getContents() ?? null;
        $mime = $upload->getClientMediaType() ?? null;
        $foto = Utils::converterBinarioParaBase64($uploadBinario, $mime);
        $dataNascimento = \DateTime::createFromFormat(Utils::FORMATOS_DATA['date'], $dados['dataNascimento']) ?? null;

        $dadosVisitante = new DadosVisitante($dados['cpf'], $dados['nome']);
        $dadosVisitante->setIdentidade($dados['identidade'] ?? null);
        $dadosVisitante->setExpedidor($dados['expedidor'] ?? null);
        $dadosVisitante->setFoto($foto);
        $dadosVisitante->setDataNascimento($dataNascimento);

        $visitante = new Visitante($dadosVisitante);
        $visitante->setCadastradoEm(new \DateTime());
        $visitante->setCadastradoPor($dados['idUsuario']);

        $resultado = $this->repositorioVisitante->adicionarVisitante($visitante);
        if (!$resultado) {
            return new Response(
                409,
                $cabecalhoResposta,
                json_encode(['error' => 'Já existe um visitante cadastrado com esse CPF']));
        }

        $resultado = $this->repositorioVisitante->buscarPorId($resultado);
        return new Response(201, $cabecalhoResposta, json_encode($resultado));
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(501);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(501);
    }

    private function filtrarPesquisa(?string $pesquisa): string
    {
        return $pesquisa && $pesquisa !== '""' ? $pesquisa : '';
    }
}
