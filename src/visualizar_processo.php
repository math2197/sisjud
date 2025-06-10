<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("ID de processo inválido.");

// Busca dados do processo com cliente
$stmt = $pdo->prepare("SELECT p.*, c.nome AS cliente_nome FROM processos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$processo = $stmt->fetch();
if (!$processo) die("Processo não encontrado.");

// Busca históricos (andamentos)
$stmt = $pdo->prepare("SELECT * FROM andamentos WHERE processo_id = ? ORDER BY data_andamento DESC, id DESC");
$stmt->execute([$id]);
$historicos = $stmt->fetchAll();

// Busca próximas atividades (tarefas)
$stmt = $pdo->prepare("SELECT * FROM tarefas WHERE processo_id = ? AND status != 'Concluída' ORDER BY data_prazo ASC");
$stmt->execute([$id]);
$atividades = $stmt->fetchAll();

// Busca documentos anexados
$stmt = $pdo->prepare("SELECT * FROM documentos WHERE processo_id = ? ORDER BY data_upload DESC");
$stmt->execute([$id]);
$docs = $stmt->fetchAll();

// --- Upload de documento ---
$msg_doc = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        $nome_arquivo = basename($_FILES['arquivo']['name']);
        $ext = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
        $novo_nome = uniqid('doc_') . '.' . $ext;
        $destino = $upload_dir . $novo_nome;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
            $stmt = $pdo->prepare("INSERT INTO documentos (processo_id, nome_arquivo, caminho) VALUES (?, ?, ?)");
            $stmt->execute([$id, $nome_arquivo, 'uploads/' . $novo_nome]);
            $msg_doc = '<div class="alert alert-success">Documento anexado com sucesso!</div>';
            $stmt = $pdo->prepare("SELECT * FROM documentos WHERE processo_id = ? ORDER BY data_upload DESC");
            $stmt->execute([$id]);
            $docs = $stmt->fetchAll();
        } else {
            $msg_doc = '<div class="alert alert-danger">Erro ao salvar o arquivo.</div>';
        }
    } else {
        $msg_doc = '<div class="alert alert-danger">Selecione um arquivo válido.</div>';
    }
}

// --- Adicionar próxima atividade (tarefa) ---
$msg_tarefa = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_tarefa'])) {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $data_prazo = $_POST['data_prazo'] ?? '';
    $usuario = $_SESSION['username'] ?? 'Usuário';

    if ($titulo && $data_prazo) {
        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, data_prazo, processo_id, usuario) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $descricao, $data_prazo, $id, $usuario]);
        $msg_tarefa = '<div class="alert alert-success">Tarefa adicionada com sucesso!</div>';
        $stmt = $pdo->prepare("SELECT * FROM tarefas WHERE processo_id = ? AND status != 'Concluída' ORDER BY data_prazo ASC");
        $stmt->execute([$id]);
        $atividades = $stmt->fetchAll();
    } else {
        $msg_tarefa = '<div class="alert alert-danger">Preencha título e prazo da tarefa.</div>';
    }
}

// Processa múltiplos requerentes/requeridos (um por linha)
$requerentes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $processo['requerente'])));
$requeridos = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $processo['requerido'])));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Visualizar Processo - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="container-fluid py-4 main-content">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($processo['titulo']); ?></h3>
            <span class="badge bg-primary"><?php echo htmlspecialchars($processo['status']); ?></span>
            <span class="badge bg-secondary"><?php echo htmlspecialchars($processo['advogado_responsavel']); ?></span>
            <span class="badge bg-info"><?php echo htmlspecialchars($processo['foro']); ?></span>
        </div>
        <div>
            <a href="processos.php" class="btn btn-light border"><i class="fa fa-arrow-left"></i></a>
            <a href="relatorio_processos_pdf.php?id=<?php echo $processo['id']; ?>" class="btn btn-light border"><i class="fa fa-print"></i></a>
            <a href="editar_processo.php?id=<?php echo $processo['id']; ?>" class="btn btn-light border"><i class="fa fa-edit"></i></a>
        </div>
    </div>

    <!-- Linha separadora para requerentes/requeridos -->
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Requerente(s):</strong>
            <?php foreach ($requerentes as $req): ?>
                <span class="badge bg-light text-dark border me-1"><?php echo htmlspecialchars($req); ?></span>
            <?php endforeach; ?>
        </div>
        <div class="col-md-6">
            <strong>Requerido(s):</strong>
            <?php foreach ($requeridos as $req): ?>
                <span class="badge bg-light text-dark border me-1"><?php echo htmlspecialchars($req); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <hr class="mb-4">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-clipboard-list text-primary"></i> Dados do Processo</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Ação</strong><br><?php echo htmlspecialchars($processo['tipo_acao']); ?><br>
                            <strong>Objeto</strong><br><?php echo nl2br(htmlspecialchars($processo['objeto'])); ?><br>
                            <strong>Número</strong><br><?php echo htmlspecialchars($processo['numero_processo']); ?><br>
                            <strong>Juízo</strong><br><?php echo htmlspecialchars($processo['juizo']); ?><br>
                            <strong>Link no tribunal</strong><br>
                            <a href="<?php echo htmlspecialchars($processo['link_tribunal']); ?>" target="_blank"><?php echo htmlspecialchars($processo['link_tribunal']); ?></a><br>
                        </div>
                        <div class="col-md-6">
                            <strong>Valor da causa</strong><br>R$ <?php echo number_format($processo['valor_causa'], 2, ',', '.'); ?><br>
                            <strong>Valor condenação</strong><br>R$ <?php echo number_format($processo['valor_condenacao'], 2, ',', '.'); ?><br>
                            <strong>Distribuído em</strong><br><?php echo $processo['distribuido_em'] ? date('d/m/Y', strtotime($processo['distribuido_em'])) : '-'; ?><br>
                            <strong>Criado em</strong><br><?php echo date('d/m/Y', strtotime($processo['created_at'])); ?><br>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-clock-rotate-left text-info"></i> Últimos históricos</h5>
                    <ul class="list-group list-group-flush">
                        <?php if ($historicos): ?>
                            <?php foreach ($historicos as $hist): ?>
                                <li class="list-group-item">
                                    <?php echo date('d/m/Y', strtotime($hist['data_andamento'])); ?> - <?php echo nl2br(htmlspecialchars($hist['descricao'])); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Nenhum histórico recente.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Próximas atividades -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa-solid fa-calendar-day text-warning"></i> Próximas atividades</h6>
                    <?php echo $msg_tarefa; ?>
                    <form method="POST" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <input type="text" name="titulo" class="form-control" placeholder="Título da tarefa" required>
                            </div>
                            <div class="col-12">
                                <textarea name="descricao" class="form-control" rows="2" placeholder="Descrição"></textarea>
                            </div>
                            <div class="col-8">
                                <input type="date" name="data_prazo" class="form-control" required>
                            </div>
                            <div class="col-4 d-grid">
                                <button type="submit" name="nova_tarefa" class="btn btn-bordo">Adicionar</button>
                            </div>
                        </div>
                    </form>
                    <ul class="list-group list-group-flush">
                        <?php if ($atividades): ?>
                            <?php foreach ($atividades as $atv): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($atv['titulo']); ?>
                                    <span class="badge bg-warning"><?php echo date('d/m', strtotime($atv['data_prazo'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Nenhuma atividade futura.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Documentos -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa-solid fa-file-lines text-primary"></i> Documentos</h6>
                    <?php echo $msg_doc; ?>
                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                        <input type="file" name="arquivo" required class="form-control mb-2">
                        <button type="submit" name="upload_doc" class="btn btn-bordo btn-sm">Anexar Documento</button>
                    </form>
                    <ul class="list-group list-group-flush">
                        <?php if ($docs): ?>
                            <?php foreach ($docs as $doc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?php echo htmlspecialchars($doc['caminho']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($doc['nome_arquivo']); ?>
                                    </a>
                                    <span class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($doc['data_upload'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Nenhum documento anexado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

