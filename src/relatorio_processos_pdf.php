<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

$where = '';
$params = [];
$periodo_str = '';
if ($data_inicio && $data_fim) {
    $where = "WHERE p.data_abertura BETWEEN ? AND ?";
    $params = [$data_inicio, $data_fim];
    $periodo_str = "Período: " . date('d/m/Y', strtotime($data_inicio)) . " a " . date('d/m/Y', strtotime($data_fim));
} else {
    $periodo_str = "Todos os processos";
}

$sql = "SELECT p.numero_processo, c.nome AS cliente_nome, c.cpf_cnpj, p.tipo_acao, p.status, p.data_abertura, p.advogado_responsavel
        FROM processos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        $where
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$processos = $stmt->fetchAll();

$sqlStatus = "SELECT p.status, COUNT(*) as total
              FROM processos p
              $where
              GROUP BY p.status";
$stmtStatus = $pdo->prepare($sqlStatus);
$stmtStatus->execute($params);
$statusData = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);

// Gráfico menor, à direita
function geraGraficoPizza($data) {
    $width = 120; $height = 120; $image = imagecreate($width, $height);
    $white = imagecolorallocate($image, 255,255,255);
    $colors = [
        imagecolorallocate($image, 128,0,32),   // Bordô         imagecolorallocate($image, 255,193,7),  // Amarelo
        imagecolorallocate($image, 25,135,84),  // Verde
        imagecolorallocate($image, 13,110,253), // Azul
        imagecolorallocate($image, 220,53,69),  // Vermelho
    ];
    $total = array_sum($data);
    $start = 0; $i = 0;
    foreach ($data as $label => $value) {
        $angle = $total ? round(($value/$total)*360) : 0;
        imagefilledarc($image, $width/2, $height/2, $width-10, $height-10, $start, $start+$angle, $colors[$i%count($colors)], IMG_ARC_PIE);
        $start += $angle; $i++;
    }
    ob_start();
    imagepng($image);
    $img_data = ob_get_clean();
    imagedestroy($image);
    return $img_data;
}
$grafico_png = geraGraficoPizza($statusData);

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SL Advocacia');
$pdf->SetTitle('Relatório de Processos');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

// Título e período centralizados
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Relatório de Processos', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $periodo_str, 0, 1, 'C');
$pdf->Ln(2);

// Gráfico à direita
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Distribuição por Status', 0, 1, 'L');
$y_before = $pdf->GetY();
$pdf->Image('@' . $grafico_png, 150, $y_before, 35, 35, 'PNG');

// Legenda à esquerda do gráfico
$pdf->SetFont('helvetica', '', 11);
$i = 0; $y_legend = $y_before;
foreach ($statusData as $label => $value) {
    $pdf->SetXY(20, $y_legend + $i*10);
    $pdf->SetFillColor(128,0,32); // Bordô padrão     if ($i == 1) $pdf->SetFillColor(255,193,7);
    if ($i == 2) $pdf->SetFillColor(25,135,84);
    if ($i == 3) $pdf->SetFillColor(13,110,253);
    if ($i == 4) $pdf->SetFillColor(220,53,69);
    $pdf->Cell(6, 6, '', 0, 0, '', 1);
    $pdf->Cell(40, 6, "$label ($value)", 0, 1, 'L', 0);
    $i++;
}
$pdf->Ln(38);

// Tabela com todos os campos
$html = '<style>
th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
td { font-size: 10px; }
</style>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
<thead>
<tr>
<th>Número</th>
<th>Cliente</th>
<th>CPF/CNPJ</th>
<th>Tipo de Ação</th>
<th>Status</th>
<th>Data de Abertura</th>
<th>Advogado</th>
</tr>
</thead><tbody>';
foreach ($processos as $p) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($p['numero_processo']) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['cliente_nome']) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['cpf_cnpj']) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['tipo_acao']) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['status']) . '</td>';
    $html .= '<td>' . date('d/m/Y', strtotime($p['data_abertura'])) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['advogado_responsavel']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$pdf->Ln(5);
$pdf->writeHTML($html, true, false, true, false, '');

// Rodapé com número da página
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Página '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 'C');

$pdf->Output('relatorio_processos.pdf', 'D');
exit;

