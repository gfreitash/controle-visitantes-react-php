<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\RespostaJson;
use App\Visitantes\Models\Visitante;
use App\Visitantes\Repositories\RepositorioVisitantePDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorVisitante extends ControladorRest
{
    private array $ERROS = [
        0 => 'Visitante não encontrado',
        1 => 'ID do usuário não informado',
        2 => 'Dados incompletos',
        3 => 'CPF inválido',
        4 => 'Já existe um visitante cadastrado com esse CPF',
        5 => 'Já existe outro visitante cadastrado com esse CPF',
        6 => 'Ordem ou tipo de ordenação inválida'
    ];

    public function __construct(private readonly RepositorioVisitante $repositorioVisitante)
    {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $queries=$request->getQueryParams();

        if (!empty($queries['id'])) {
            $resultado = $this->obterVisitantePorId($queries['id']);
        } elseif (!empty($queries['cpf'])) {
            $resultado = $this->obterVisitantePorCpf($queries['cpf']);
            if ($resultado instanceof RespostaJson) {
                return $resultado;
            }
        } else {
            return $this->obterTodosVisitantes($queries);
        }

        if (!$resultado) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[0]]));
        }

        return new RespostaJson(200, json_encode($resultado));
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $dados = $request->getParsedBody();

        if (empty($dados['idUsuario'])) {
            return new RespostaJson(403, json_encode(['error' => $this->ERROS[1]]));
        }

        if (empty($dados['nome']) || empty($dados['cpf'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }

        if (!DadosVisitante::validarCPF($dados['cpf'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[3]]));
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

        $resultado = $this->repositorioVisitante->criar($visitante);
        if (!$resultado) {
            return new RespostaJson(409, json_encode(['error' => $this->ERROS[4]]));
        }

        $resultado = $this->repositorioVisitante->buscarPorId($resultado);
        return new RespostaJson(201, json_encode($resultado));
    }

    public function put(ServerRequestInterface $request): ResponseInterface
    {
        global $_PUT;
        $dados = $_PUT;

        if (empty($dados['idUsuario'])) {
            return new RespostaJson(403, json_encode(['error' => $this->ERROS[1]]));
        }
        if (empty($dados['nome']) || empty($dados['cpf'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[2]]));
        }
        if (!DadosVisitante::validarCPF($dados['cpf'])) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[3]]));
        }

        if ($dados['id']) {
            $visitante = $this->repositorioVisitante->buscarPorId($dados['id']);
        } else {
            $visitante = $this->repositorioVisitante->buscarPorCpf($dados['cpf']);
        }

        if (!$visitante) {
            return new RespostaJson(404, json_encode(['error' => $this->ERROS[0]]));
        }

        $upload = $request->getUploadedFiles()['fotoInput'];
        if ($upload) {
            $uploadBinario = $upload->getStream()->getContents() ?? null;
            $mime = $upload->getClientMediaType() ?? null;
            $foto = Utils::converterBinarioParaBase64($uploadBinario, $mime);
        } elseif ($dados['excluirFoto'] !== "false") {
            $foto = "";
        } else {
            $foto = $visitante->getDadosVisitante()->getFoto();
        }

        $dataNascimento = \DateTime::createFromFormat(Utils::FORMATOS_DATA['date'], $dados['dataNascimento']) ?? null;

        $dadosVisitante = $visitante->getDadosVisitante();
        $dadosVisitante->setCpf($dados['cpf']);
        $dadosVisitante->setNome($dados['nome']);
        $dadosVisitante->setIdentidade($dados['identidade'] ?? null);
        $dadosVisitante->setExpedidor($dados['expedidor'] ?? null);
        $dadosVisitante->setFoto($foto);
        $dadosVisitante->setDataNascimento($dataNascimento);

        $visitante->setDadosVisitante($dadosVisitante);
        $visitante->setModificadoEm(new \DateTime());
        $visitante->setModificadoPor($dados['idUsuario']);

        $resultado = $this->repositorioVisitante->atualizar($visitante);
        if (!$resultado) {
            return new RespostaJson(409, json_encode(['error' => $this->ERROS[6]]));
        }

        return new RespostaJson(200, json_encode($visitante));
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        return $this->_501;
    }

    private function filtrarPesquisa(?string $pesquisa): string
    {
        return $pesquisa && $pesquisa !== '""' ? $pesquisa : '';
    }

    private function obterVisitantePorId(string $id): bool|Visitante
    {
        $resultado = $this->repositorioVisitante->buscarPorId($id);
        if ($resultado) {
            $resultado->setFormatoData(Utils::FORMATOS_DATA['web']);
            $resultado->getDadosVisitante()->setFormatoData(Utils::FORMATOS_DATA['date']);
        }
        return $resultado;
    }

    private function obterVisitantePorCpf(string $cpf): bool|RespostaJson|Visitante
    {
        if (!DadosVisitante::validarCPF($cpf)) {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[3]]));
        }
        $resultado = $this->repositorioVisitante->buscarPorCpf($cpf);
        if ($resultado) {
            $resultado->setFormatoData(Utils::FORMATOS_DATA['web']);
            $resultado->getDadosVisitante()->setFormatoData(Utils::FORMATOS_DATA['date']);
        }
        return $resultado;
    }

    private function obterTodosVisitantes(array $queries): RespostaJson
    {
        $pesquisa = $this->filtrarPesquisa($queries['pesquisa']);
        $ordenarPor = $queries['ordenar'] ?? 'nome';
        $limite = $queries['limite'] ?? null;
        $pagina = $queries['pagina'] ?? 1;
        $ordem = $queries['ordem'] ?? 'ASC';
        $buscarPor = RepositorioVisitantePDO::BUSCAR_POR;

        $offset = ($pagina - 1) * $limite;
        if (array_key_exists($ordenarPor, $buscarPor) && in_array(strtoupper($ordem), ['ASC', 'DESC'])) {
            $buscarPor = [$ordenarPor => $ordem] + $buscarPor;
        } else {
            return new RespostaJson(400, json_encode(['error' => $this->ERROS[6]]));
        }

        $quantidadeVisitantes = $this->repositorioVisitante->obterTotal($pesquisa ?? '');
        if ($limite) {
            $resultado = $pesquisa
                ? $this->repositorioVisitante->buscarComo(
                    $pesquisa,
                    new ParametroBusca($buscarPor, $limite, $offset)
                )
                : $this->repositorioVisitante->buscarTodos(
                    new ParametroBusca($buscarPor, $limite, $offset)
                );
            $quantidadePaginas = ceil($quantidadeVisitantes / $limite);
        } else {
            $resultado = $pesquisa
                ? $this->repositorioVisitante->buscarComo($pesquisa, new ParametroBusca($buscarPor))
                : $this->repositorioVisitante->buscarTodos(new ParametroBusca($buscarPor));
            $quantidadePaginas = 1;
        }
        $conteudoResposta = [
            'quantidadeTotal' => $quantidadeVisitantes,
            'quantidadePaginas' => $quantidadePaginas,
            'paginaAtual' => $pagina,
            'dados' => $resultado
        ];

        return new RespostaJson(200, json_encode($conteudoResposta));
    }
}
