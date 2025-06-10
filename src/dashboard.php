<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

// Dados para cards
$total_processos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$total_andamentos = $pdo->query("SELECT COUNT(*) FROM andamentos")->fetchColumn();
$total_tarefas = $pdo->query("SELECT COUNT(*) FROM tarefas")->fetchColumn();

// Dados para gráficos
$statusData = $pdo->query("SELECT status, COUNT(*) as total FROM processos GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$tipoData = $pdo->query("SELECT tipo_acao, COUNT(*) as total FROM processos GROUP BY tipo_acao")->fetchAll(PDO::FETCH_KEY_PAIR);

// Dados para prazos do dia
$prazosHoje = $pdo->query("SELECT * FROM tarefas WHERE data_prazo = CURDATE() AND status != 'Concluída'")->fetchAll();

// Dados para movimentações recentes
$andamentosRecentes = $pdo->query("SELECT a.*, p.numero_processo FROM andamentos a JOIN processos p ON a.processo_id = p.id ORDER BY a.data_andamento DESC, a.id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-icon {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        .card-text.display-6 {
            font-size: 2.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2 class="mb-4">Painel de Controle</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-white d-flex flex-row align-items-center" style="background-color: #800020;">
                <i class="fa-solid fa-scale-balanced card-icon"></i>
                <div class="card-body">
                    <h5 class="card-title">Processos</h5>
                    <p class="card-text display-6"><?php echo $total_processos; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white d-flex flex-row align-items-center" style="background-color: #800020;">
                <i class="fa-solid fa-user card-icon"></i>
                <div class="card-body">
                    <h5 class="card-title">Clientes</h5>
                    <p class="card-text display-6"><?php echo $total_clientes; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white d-flex flex-row align-items-center" style="background-color: #800020;">
                <i class="fa-solid fa-file-lines card-icon"></i>
                <div class="card-body">
                    <h5 class="card-title">Andamentos</h5>
                    <p class="card-text display-6"><?php echo $total_andamentos; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white d-flex flex-row align-items-center" style="background-color: #800020;">
                <i class="fa-solid fa-list-check card-icon"></i>
                <div class="card-body">
                    <h5 class="card-title">Tarefas</h5>
                    <p class="card-text display-6"><?php echo $total_tarefas; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <canvas id="graficoStatus"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="graficoTipo"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h5><i class="fa-solid fa-bell text-warning"></i> Prazos do Dia</h5>
            <ul class="list-group">
                <?php foreach ($prazosHoje as $p): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($p['titulo']); ?>
                    <span class="badge bg-warning"><?php echo date('d/m', strtotime($p['data_prazo'])); ?></span>
                </li>
                <?php endforeach; ?>
                <?php if (empty($prazosHoje)): ?>
                <li class="list-group-item text-muted">Nenhum prazo para hoje.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h5><i class="fa-solid fa-clock-rotate-left text-info"></i> Movimentações Recentes</h5>
            <ul class="list-group">
                <?php foreach ($andamentosRecentes as $a): ?>
                <li class="list-group-item">
                    <strong><?php echo htmlspecialchars($a['numero_processo']); ?></strong> - <?php echo date('d/m/Y', strtotime($a['data_andamento'])); ?>
                    <br />
                    <?php echo htmlspecialchars(mb_strimwidth($a['descricao'], 0, 60, '...')); ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($andamentosRecentes)): ?>
                <li class="list-group-item text-muted">Nenhuma movimentação recente.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
const statusLabels = <?php echo json_encode(array_keys($statusData)); ?>;
const statusValues = <?php echo json_encode(array_values($statusData)); ?>;
const tipoLabels = <?php echo json_encode(array_keys($tipoData)); ?>;
const tipoValues = <?php echo json_encode(array_values($tipoData)); ?>;

new Chart(document.getElementById('graficoStatus'), {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: ['#800020', '#ffc107', '#198754', '#0d6efd', '#dc3545']
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

new Chart(document.getElementById('graficoTipo'), {
    type: 'bar',
    data: {
        labels: tipoLabels,
        datasets: [{
            label: 'Processos por Tipo',
            data: tipoValues,
            backgroundColor: '#800020'
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                precision: 0
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

