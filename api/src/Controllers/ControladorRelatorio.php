<?php

namespace App\Visitantes\Controllers;

use App\Visitantes\Helpers\Utils;
use App\Visitantes\Interfaces\ControladorRest;
use App\Visitantes\Interfaces\RepositorioUsuario;
use App\Visitantes\Interfaces\RepositorioVisita;
use App\Visitantes\Interfaces\RepositorioVisitante;
use App\Visitantes\Models\DadosVisitante;
use App\Visitantes\Models\ParametroBusca;
use App\Visitantes\Models\RespostaJson;
use App\Visitantes\Models\Visita;
use App\Visitantes\Repositories\RepositorioVisitantePDO;
use App\Visitantes\Repositories\RepositorioVisitaPDO;
use Nyholm\Psr7\Response;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorRelatorio extends ControladorRest
{

    public function __construct(
        private readonly RepositorioVisita $repositorioVisita,
        private readonly RepositorioVisitante $repositorioVisitante,
        private readonly RepositorioUsuario $repositorioUsuario
    ) {
        parent::__construct();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        //Os dados esperados podem ser qualquer um destes: cpf, dataInicio, dataFim, relatorio, status
        $dados = $request->getQueryParams();

        if ($dados['relatorio'] === 'visitas') {
            return $this->obterRelatorioVisitas($dados);
        } elseif ($dados['relatorio'] === 'visitantes') {
            return $this->obterRelatorioVisitantes($dados);
        } else {
            return new RespostaJson(400, json_encode(['error' => 'Relatório inválido']));
        }
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

    public function obterRelatorioVisitas(array $dados): ResponseInterface
    {

        $statusValidos = ['abertas', 'realizadas'];
        if (!in_array($dados['status'], $statusValidos)) {
            return new RespostaJson(400, json_encode(['error' => 'Status inválido']));
        }

        $status = $dados['status'] === 'abertas' ? Visita::STATUS[1] : "";
        $inicio = Utils::tentarCriarDateTime($dados['dataInicio'] ?? null);
        $fim = Utils::tentarCriarDateTime($dados['dataFim'] ?? null);
        $cpf = $dados['cpf'] ?? "";

        if ($cpf && DadosVisitante::validarCPF($cpf)) {
            $visitante = $this->repositorioVisitante->buscarPorCpf($cpf);
            if (!$visitante) {
                return new RespostaJson(404, json_encode(['error' => 'Visitante não encontrado']));
            }
            $visitas = $this->repositorioVisita->buscarTodasDeVisitante(
                $visitante,
                $status,
                new ParametroBusca(RepositorioVisitaPDO::BUSCAR_POR_JOIN, dataInicio: $inicio, dataFim: $fim)
            );
        } else {
            $visitas = $this->repositorioVisita->buscarTodas(
                $status,
                new ParametroBusca(RepositorioVisitaPDO::BUSCAR_POR_JOIN, dataInicio: $inicio, dataFim: $fim)
            );
        }

        return $this->emitirRelatorio(
            $this->preencherPlanilhaVisita(...),
            $visitas,
            'Relatório de Visitas',
            $this->estilizarPlanilha(...)
        );
    }

    public function obterRelatorioVisitantes(array $dados): Response
    {
        $statusValidos = ['ativos', 'cadastrados'];
        if (!empty($dados['status']) && !in_array($dados['status'], $statusValidos)) {
            return new RespostaJson(400, json_encode(['erro' => 'Status inválido']));
        }

        $status = $dados['status'] ?? $statusValidos[1];
        $inicio = Utils::tentarCriarDateTime($dados['dataInicio'] ?? null);
        $fim = Utils::tentarCriarDateTime($dados['dataFim'] ?? null);
        $parametros = new ParametroBusca(RepositorioVisitantePDO::BUSCAR_POR, dataInicio: $inicio, dataFim: $fim);

        if ($status === 'ativos') {
            $visitantes = $this->repositorioVisita->buscarVisitantesAtivos($parametros);
        } else { //$status === 'cadastrados'
            $visitantes = $this->repositorioVisitante->buscarTodos($parametros);
        }

        return $this->emitirRelatorio(
            $this->preencherPlanilhaVisitante(...),
            $visitantes,
            'Relatório de Visitantes'
        );
    }

    /**
     * @throws Exception
     */
    private function estilizarPlanilha(Worksheet $sheet, string $titulo, int $qtdColunas, int $qtdItens)
    {
        $col = 'a';
        for ($i = 0; $i < $qtdColunas-1; $i++) {
            $col++;
        }
        $linhaInicial = 3;
        $linhaFinal = $linhaInicial + $qtdItens - 1;

        //Titulo
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells("A1:{$col}1");
        $sheet->getStyle('A1')->getFont()->setSize(24);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');
        $sheet->getRowDimension('1')->setRowHeight(37.5);
        $sheet->getStyle('A1')->getFill()->setFillType('solid')->getStartColor()->setRGB('8ea9db');
        $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        //Cabeçalho
        $coordCabecalho = "A2:{$col}2";
        $sheet->getStyle($coordCabecalho)->getFont()->setBold(true);
        $sheet->getStyle($coordCabecalho)->getFill()->setFillType('solid')->getStartColor()->setRGB('b4c6e7');
        $sheet->getStyle($coordCabecalho)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($coordCabecalho)->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($coordCabecalho)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension('2')->setRowHeight(34);

        //AutoFilter
        $coordDados = "A2:$col$linhaFinal";
        $sheet->setAutoFilter($coordDados);
        //Estilos
        $coordDados = "A$linhaInicial:$col$linhaFinal";
        $sheet->getStyle($coordDados)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($coordDados)->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($coordDados)->getAlignment()->setWrapText(true);

        //Adicionar listras independentemente das linhas visíveis
        /** @var Wizard\Expression $wizardLighter */
        $wizardLighter = (new Wizard($coordDados))->newRule(Wizard::EXPRESSION);
        $lightStyle = new Style(false, true);
        $lightStyle->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB("FFF2F2F2");
        $wizardLighter->expression('MOD(SUBTOTAL(3,$A$3:$A3),2)=0')->setStyle($lightStyle);
        /** @var Wizard\Expression $wizardDarker */
        $wizardDarker = (new Wizard($coordDados))->newRule(Wizard::EXPRESSION);
        $darkStyle = new Style(false, true);
        $darkStyle->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB("FFD9D9D9");
        $wizardDarker->expression('MOD(SUBTOTAL(3,$A$3:$A3),2)=1')->setStyle($darkStyle);

        $condicoes = [$wizardLighter->getConditional(), $wizardDarker->getConditional()];
        $sheet->getStyle($coordDados)->setConditionalStyles($condicoes);

        //Finalmente, definir a célula ativa
        $sheet->setSelectedCell("A1");
    }

    /**
     * @param Worksheet $sheet
     * @param array $visitas
     * @return int Quantidade de colunas
     */
    private function preencherPlanilhaVisita(Worksheet $sheet, array $visitas): int
    {
        //Cabecalho
        $sheet->getRowDimension('2')->setRowHeight(34);
        $sheet->setCellValue('A2', 'ID');
        $sheet->getColumnDimension('A')->setWidth(11);
        $sheet->setCellValue('B2', 'CPF');
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->setCellValue('C2', 'Nome');
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->setCellValue('D2', 'Data de Nascimento');
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->setCellValue('E2', 'Identidade');
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->setCellValue('F2', 'Expedidor');
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->setCellValue('G2', 'Sala da Visita');
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->setCellValue('H2', 'Motivo da Visita');
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->setCellValue('I2', 'Data da Visita');
        $sheet->getColumnDimension('I')->setWidth(19);
        $sheet->setCellValue('J2', 'Foi Liberado');
        $sheet->getColumnDimension('J')->setWidth(9);
        $sheet->setCellValue('K2', 'Cadastrada Por');
        $sheet->getColumnDimension('K')->setWidth(21);
        $sheet->setCellValue('L2', 'Modificada Em');
        $sheet->getColumnDimension('L')->setWidth(19);
        $sheet->setCellValue('M2', 'Modificada Por');
        $sheet->getColumnDimension('M')->setWidth(21);
        $sheet->setCellValue('N2', 'Finalizada Em');
        $sheet->getColumnDimension('N')->setWidth(19);
        $sheet->setCellValue('O2', 'Finalizada Por');
        $sheet->getColumnDimension('O')->setWidth(21);

        //Dados
        $linhaInicial = 3;
        $qtdLinhas = count($visitas);
        for ($i = 0; $i < $qtdLinhas; $i++) {
            $row = $linhaInicial + $i;
            $visita = $visitas[$i];
            $visitante = $this->repositorioVisitante->buscarPorCpf($visitas[$i]->cpf);
            if (!$visitante) {
                continue;
            }
            $visitante = (object) $visitante->paraArray();

            $cadastradaPor = (object) $this->repositorioUsuario
                ->buscarPorId($visita->cadastrada_por)
                ->paraArray();
            $modificadaPor = $visita->modificada_por ? (object) $this->repositorioUsuario
                ->buscarPorId($visita->modificada_por)
                ->paraArray() : null;
            $finalizadaPor = $visita->finalizada_por ? (object) $this->repositorioUsuario
                ->buscarPorId($visita->finalizada_por)
                ->paraArray() : null;

            $dataNascimento = Utils::formatarData(
                $visitante?->data_nascimento,
                Utils::FORMATOS_DATA['date'],
                Utils::FORMATOS_DATA['date_local']
            );
            $dataVisita = Utils::formatarData(
                $visita->data_visita,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );
            $dataModificada = Utils::formatarData(
                $visita->modificada_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );
            $dataFinalizada = Utils::formatarData(
                $visita->finalizada_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );

            $sheet->setCellValue("A$row", $visita->id);
            $sheet->setCellValue("B$row", DadosVisitante::mascaraCpf($visitante?->cpf));
            $sheet->setCellValue("C$row", $visitante?->nome);
            $sheet->setCellValue("D$row", $dataNascimento);
            $sheet->setCellValue("E$row", Utils::seNuloRetornarVazio($visitante?->identidade));
            $sheet->setCellValue("F$row", Utils::seNuloRetornarVazio($visitante?->expedidor));
            $sheet->setCellValue("G$row", $visita->sala_visita);
            $sheet->setCellValue("H$row", $visita->motivo_visita);
            $sheet->setCellValue("I$row", $dataVisita);
            $sheet->setCellValue("J$row", $visita->foi_liberado ? 'Sim' : 'Não');
            $sheet->setCellValue("K$row", $cadastradaPor->nome);
            $sheet->setCellValue("L$row", $dataModificada);
            $sheet->setCellValue("M$row", Utils::seNuloRetornarVazio($modificadaPor?->nome));
            $sheet->setCellValue("N$row", $dataFinalizada);
            $sheet->setCellValue("O$row", Utils::seNuloRetornarVazio($finalizadaPor?->nome));
        }

        return 15;
    }

    /**
     * @param Worksheet $sheet
     * @param array $visitantes
     * @return int Quantidade de colunas
     */
    private function preencherPlanilhaVisitante(Worksheet $sheet, array $visitantes): int
    {
        //Cabeçalho
        $sheet->getRowDimension('2')->setRowHeight(34);
        $sheet->setCellValue('A2', 'Nº da Visita');
        $sheet->getColumnDimension('A')->setWidth(11);
        $sheet->setCellValue('B2', 'CPF');
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->setCellValue('C2', 'Nome');
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->setCellValue('D2', 'Data de Nascimento');
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->setCellValue('E2', 'Identidade');
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->setCellValue('F2', 'Expedidor');
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->setCellValue('G2', 'Cadastrado Em');
        $sheet->getColumnDimension('G')->setWidth(19);
        $sheet->setCellValue('H2', 'Cadastrado Por');
        $sheet->getColumnDimension('H')->setWidth(21);
        $sheet->setCellValue('I2', 'Modificado Em');
        $sheet->getColumnDimension('I')->setWidth(19);
        $sheet->setCellValue('J2', 'Modificado Por');
        $sheet->getColumnDimension('J')->setWidth(21);

        //Dados
        $linhaInicial = 3;
        $qtdLinhas = count($visitantes);
        for ($i = 0; $i < $qtdLinhas; $i++) {
            $row = $linhaInicial + $i;
            $visitante = $visitantes[$i];

            $cadastradoPor = (object) $this->repositorioUsuario
                ->buscarPorId($visitante->cadastrado_por)
                ->paraArray();
            $modificadoPor = $visitante->modificado_por ? (object) $this->repositorioUsuario
                ->buscarPorId($visitante->modificado_por)
                ->paraArray() : null;

            $dataNascimento = Utils::formatarData(
                $visitante->data_nascimento,
                Utils::FORMATOS_DATA['date'],
                Utils::FORMATOS_DATA['date_local']
            );
            $dataCadastrado = Utils::formatarData(
                $visitante->cadastrado_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );
            $dataModificado = Utils::formatarData(
                $visitante->modificado_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );

            $sheet->setCellValue("A$row", $visitante->id);
            $sheet->setCellValue("B$row", DadosVisitante::mascaraCpf($visitante->cpf));
            $sheet->setCellValue("C$row", $visitante->nome);
            $sheet->setCellValue("D$row", $dataNascimento);
            $sheet->setCellValue("E$row", Utils::seNuloRetornarVazio($visitante->identidade));
            $sheet->setCellValue("F$row", Utils::seNuloRetornarVazio($visitante->expedidor));
            $sheet->setCellValue("G$row", $dataCadastrado);
            $sheet->setCellValue("H$row", $cadastradoPor->nome);
            $sheet->setCellValue("I$row", $dataModificado);
            $sheet->setCellValue("J$row", Utils::seNuloRetornarVazio($modificadoPor?->nome));
        }

        return 10;
    }

    private function emitirRelatorio(
        callable $preencherDados,
        array $lista,
        string $nomeArquivo
    ): Response
    {
        try {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $sheet = $spreadsheet->getActiveSheet();

            $qtdColunas = $preencherDados($sheet, $lista);
            $qtdLinhas = count($lista);
            $this->estilizarPlanilha($sheet, $nomeArquivo, $qtdColunas, $qtdLinhas);

            //Salvar
            ob_clean();
            ob_start();
            $data = (new \DateTime())->format('Y.m.d H-i-s');
            $nomeArquivo = "$nomeArquivo ($data).xlsx";
            $arquivo = fopen("../tmp/$nomeArquivo", 'w');
            $writer = new Xlsx($spreadsheet);
            $writer->save($arquivo);
            fclose($arquivo);

            $tamanho = filesize("../tmp/$nomeArquivo");
            readfile("../tmp/$nomeArquivo");
            unlink("../tmp/$nomeArquivo");

            return new Response(200, [
                'Content-Type' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename='.rawurlencode($nomeArquivo),
                'Content-Length' => $tamanho,
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
            ]);
        } catch (\Exception $e) {
            return new RespostaJson(500, json_encode(['error' => $e->getMessage()]));
        }
    }
}
