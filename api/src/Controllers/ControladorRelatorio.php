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
            return $this->_501;
            //TODO return $this->obterRelatorioVisitantes($dados);
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
        $inicio = Utils::tentarCriarDateTime($dados['dataInicio']);
        $fim = Utils::tentarCriarDateTime($dados['dataFim']);
        $cpf = $dados['cpf'] ?? "";

        if ($cpf && DadosVisitante::validarCPF($cpf)) {
            $visitante = $this->repositorioVisitante->buscarPorCpf($cpf);
            if (!$visitante) {
                return new RespostaJson(404, json_encode(['error' => 'Visitante não encontrado']));
            }
            $visitas = $this->repositorioVisita->obterTodasVisitasDeVisitante(
                $visitante,
                $status,
                new ParametroBusca(RepositorioVisitaPDO::BUSCAR_POR_JOIN, dataInicio: $inicio, dataFim: $fim)
            );
        } else {
            $visitas = $this->repositorioVisita->obterTodasVisitas(
                $status,
                new ParametroBusca(RepositorioVisitaPDO::BUSCAR_POR_JOIN, dataInicio: $inicio, dataFim: $fim)
            );
        }

        try {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $sheet = $spreadsheet->getActiveSheet();
            @$this->configurarEstilosPlanilhaVisitas($sheet, $visitas);

            //Salvar
            ob_clean();
            ob_start();
            $data = (new \DateTime())->format('Y.m.d H-i-s');
            $nomeArquivo = "Relatório de Visitas ($data).xlsx";
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

    /**
     * @throws Exception
     */
    private function configurarEstilosPlanilhaVisitas(Worksheet $sheet, array $visitas): void
    {

        //Titulo
        $sheet->setCellValue('A1', 'Relatório de visitas');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setSize(24);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');
        $sheet->getRowDimension('1')->setRowHeight(37.5);
        $sheet->getStyle('A1')->getFill()->setFillType('solid')->getStartColor()->setRGB('8ea9db');
        $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        //Cabecalho
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

        $coordCabecalho = 'A2:O2';
        $sheet->getStyle($coordCabecalho)->getFont()->setBold(true);
        $sheet->getStyle($coordCabecalho)->getFill()->setFillType('solid')->getStartColor()->setRGB('b4c6e7');
        $sheet->getStyle($coordCabecalho)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($coordCabecalho)->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($coordCabecalho)->getAlignment()->setWrapText(true);

        //Dados
        $linhaInicial = 3;
        $qtdLinhas = count($visitas);
        for ($i = 0; $i < $qtdLinhas; $i++) {
            $row = $linhaInicial + $i;
            $visitante = $this->repositorioVisitante->buscarPorCpf($visitas[$i]->cpf);
            $visitante = $visitante ? $visitante->paraArray() : [];

            $cadastradaPor = $this->repositorioUsuario
                ->buscarPorId($visitas[$i]->cadastrada_por)
                ->paraArray();
            $modificadaPor = $visitas[$i]->modificada_por ? $this->repositorioUsuario
                ->buscarPorId($visitas[$i]->modificada_por)
                ->paraArray() : [];
            $finalizadaPor = $visitas[$i]->finalizada_por ? $this->repositorioUsuario
                ->buscarPorId($visitas[$i]->finalizada_por)
                ->paraArray() : [];

            $dataNascimento = Utils::formatarData(
                $visitante['data_nascimento'],
                Utils::FORMATOS_DATA['date'],
                Utils::FORMATOS_DATA['date_local']
            );
            $dataVisita = Utils::formatarData(
                $visitas[$i]->data_visita,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );
            $dataModificada = Utils::formatarData(
                $visitas[$i]->modificada_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );
            $dataFinalizada = Utils::formatarData(
                $visitas[$i]->finalizada_em,
                Utils::FORMATOS_DATA['datetime'],
                Utils::FORMATOS_DATA['datetime_local']
            );

            $sheet->setCellValue("A$row", $visitas[$i]->id);
            $sheet->setCellValue("B$row", DadosVisitante::mascaraCpf($visitas[$i]->cpf));
            $sheet->setCellValue("C$row", $visitante['nome']);
            $sheet->setCellValue("D$row", $dataNascimento);
            $sheet->setCellValue("E$row", Utils::seNuloRetornarVazio($visitante['identidade']));
            $sheet->setCellValue("F$row", Utils::seNuloRetornarVazio($visitante['expedidor']));
            $sheet->setCellValue("G$row", $visitas[$i]->sala_visita);
            $sheet->setCellValue("H$row", $visitas[$i]->motivo_visita);
            $sheet->setCellValue("I$row", $dataVisita);
            $sheet->setCellValue("J$row", $visitas[$i]->foi_liberado ? 'Sim' : 'Não');
            $sheet->setCellValue("K$row", $cadastradaPor['nome']);
            $sheet->setCellValue("L$row", $dataModificada);
            $sheet->setCellValue("M$row", Utils::seNuloRetornarVazio($modificadaPor['nome']));
            $sheet->setCellValue("N$row", $dataFinalizada);
            $sheet->setCellValue("O$row", Utils::seNuloRetornarVazio($finalizadaPor['nome']));
        }

        //AutoFilter
        $linhaFinal = $linhaInicial + $qtdLinhas - 1;
        trigger_error("Linha Final: $linhaFinal");
        $coordDados = "A2:O$linhaFinal";
        $sheet->setAutoFilter($coordDados);
        //Estilos
        $coordDados = "A$linhaInicial:O$linhaFinal";
        $sheet->getStyle($coordDados)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($coordDados)->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($coordDados)->getAlignment()->setWrapText(true);

        //Adicionar listras independentemente das linhas visíveis
        /** @var Wizard\Expression $wizardLighter */
        $wizardLighter = (new Wizard($coordDados))->newRule(Wizard::EXPRESSION);
        $lightStyle = new Style(false, false);
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

    public function obterRelatorioVisitantes(array $dados)
    {
        //TODO - Implementar
    }
}
