<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Recebe datas do formulário (opcional, igual ao PDF)
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

$where = '';
$params = [];
if ($data_inicio && $data_fim) {
    $where = "WHERE p.data_abertura BETWEEN ? AND ?";
    $params = [$data_inicio, $data_fim];
}

// Busca dados dos processos
$sql = "SELECT p.numero_processo, c.nome AS cliente_nome, c.cpf_cnpj, p.tipo_acao, p.status, p.data_abertura, p.advogado_responsavel, p.observacoes
        FROM processos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        $where
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$processos = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cabeçalhos
$sheet->setCellValue('A1', 'Número do Processo');
$sheet->setCellValue('B1', 'Cliente');
$sheet->setCellValue('C1', 'CPF/CNPJ');
$sheet->setCellValue('D1', 'Tipo de Ação');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Data de Abertura');
$sheet->setCellValue('G1', 'Advogado');
$sheet->setCellValue('H1', 'Observações');

// Dados
$row = 2;
foreach ($processos as $p) {
    $sheet->setCellValue('A' . $row, $p['numero_processo']);
    $sheet->setCellValue('B' . $row, $p['cliente_nome']);
    $sheet->setCellValue('C' . $row, $p['cpf_cnpj']);
    $sheet->setCellValue('D' . $row, $p['tipo_acao']);
    $sheet->setCellValue('E' . $row, $p['status']);
    $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($p['data_abertura'])));
    $sheet->setCellValue('G' . $row, $p['advogado_responsavel']);
    $sheet->setCellValue('H' . $row, $p['observacoes']);
    $row++;
}

// Ajusta largura automática das colunas
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Define cabeçalhos para download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_processos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

