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
            $ordenarPor = $queries['ordenar'] ?? 'nome';
            $limite = $queries['limite'] ?? 30;
            $pagina = $queries['pagina'] ?? 1;
            $ordem = $queries['ordem'] ?? 'DESC';
            $buscarPor = RepositorioVisitantePDO::BUSCAR_POR;


            $offset = $pagina ? ($pagina - 1) * $limite : 0;
            if (array_key_exists($ordenarPor, $buscarPor)) {
                if (in_array($ordem, ['ASC', 'DESC'])) {
                    $ordenar = [$ordenarPor => $ordem];
                    array_shift($buscarPor);
                    array_unshift($buscarPor, $ordenar);
                } else {
                    return new Response(
                        400,
                        $cabecalhoResposta,
                        json_encode(['error' => 'Ordem inválida']));
                }
            } else {
                return new Response(
                    400,
                    $cabecalhoResposta,
                    json_encode(['error' => 'Tipo de ordenação inválida']));
            }

            $quantidadeVisitantes = $this->repositorioVisitante->obterTotalVisitantes();
            $quantidadePaginas = ceil($quantidadeVisitantes / $limite);
            $resultado = $this->repositorioVisitante->buscarTodosVisitantes($buscarPor, $limite, $offset);

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
        $foto = base64_encode($upload->getStream()->getContents()) ?? null;
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
}
