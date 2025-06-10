<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("ID de processo inválido.");

$stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
$stmt->execute([$id]);
$processo = $stmt->fetch();
if (!$processo) die("Processo não encontrado.");

// Busca clientes para o select
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $numero_processo = $_POST['numero_processo'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? null;
    $tipo_acao = $_POST['tipo_acao'] ?? '';
    $objeto = $_POST['objeto'] ?? '';
    $status = $_POST['status'] ?? 'Em andamento';
    $juizo = $_POST['juizo'] ?? null;
    $foro = $_POST['foro'] ?? null;
    $link_tribunal = $_POST['link_tribunal'] ?? null;
    $valor_causa = $_POST['valor_causa'] ?? null;
    $valor_condenacao = $_POST['valor_condenacao'] ?? null;
    $distribuido_em = $_POST['distribuido_em'] ?? null;
    $requerente = $_POST['requerente'] ?? null;
    $requerido = $_POST['requerido'] ?? null;
    $observacoes = $_POST['observacoes'] ?? null;
    $advogado_responsavel = $_POST['advogado_responsavel'] ?? null;

    try {
        $stmt = $pdo->prepare("UPDATE processos SET
            numero_processo=?, titulo=?, cliente_id=?, tipo_acao=?, objeto=?, status=?, juizo=?, foro=?, link_tribunal=?, valor_causa=?, valor_condenacao=?, distribuido_em=?, requerente=?, requerido=?, observacoes=?, advogado_responsavel=?
            WHERE id=?");
        $stmt->execute([
            $numero_processo, $titulo, $cliente_id, $tipo_acao, $objeto, $status, $juizo, $foro, $link_tribunal,
            $valor_causa, $valor_condenacao, $distribuido_em, $requerente, $requerido, $observacoes, $advogado_responsavel, $id
        ]);
        header('Location: visualizar_processo.php?id=' . $id);
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar processo: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Processo - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
    .editar-wrapper {
        margin-left: 180px;
        margin-top: 68px;
        padding: 0 0 30px 0;
        width: 100%;
    }
    .card-editar {
        border-radius: 18px;
        box-shadow: 0 2px 12px #0001;
        border: none;
        padding: 2.5rem 2.5rem 1.5rem 2.5rem;
        background: #fff;
        transition: box-shadow .2s;
        width: 100%;
        max-width: 100%;
    }
    .form-label {
        font-size: 0.98rem;
        color: #800020;
        font-weight: 500;
    }
    .btn-bordo {
        background-color: #800020 !important;
        border-color: #800020 !important;
        color: #fff !important;
        border-radius: 8px;
        font-size: 1rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: background .2s, color .2s;
    }
    .btn-bordo:hover {
        background-color: #a8324a !important;
        color: #fff !important;
    }
    .btn-secondary {
        border-radius: 8px;
        font-size: 1rem;
        padding: 0.5rem 1.2rem;
    }
    @media (max-width: 991.98px) {
        .editar-wrapper { margin-left: 0; padding: 0 0 20px 0; }
        .card-editar { padding: 1.2rem 0.7rem 0.7rem 1rem; }
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="editar-wrapper main-content">
    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>
    <form method="POST" class="bg-white shadow-sm p-4 rounded-4 card-editar" style="width:100%;">
        <h4 class="fw-bold mb-4" style="color:#800020;">Editar Processo</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Título:</label>
                <input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($processo['titulo']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Número do Processo:</label>
                <input type="text" name="numero_processo" class="form-control" required value="<?php echo htmlspecialchars($processo['numero_processo']); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Cliente:</label>
                <select name="cliente_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $cli): ?>
                        <option value="<?php echo $cli['id']; ?>" <?php if ($cli['id'] == $processo['cliente_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cli['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de Ação:</label>
                <input type="text" name="tipo_acao" class="form-control" required value="<?php echo htmlspecialchars($processo['tipo_acao']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Objeto:</label>
                <textarea name="objeto" class="form-control" required><?php echo htmlspecialchars($processo['objeto']); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Juízo:</label>
                <input type="text" name="juizo" class="form-control" value="<?php echo htmlspecialchars($processo['juizo']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Foro:</label>
                <input type="text" name="foro" class="form-control" value="<?php echo htmlspecialchars($processo['foro']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Link no Tribunal:</label>
                <input type="url" name="link_tribunal" class="form-control" value="<?php echo htmlspecialchars($processo['link_tribunal']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor da Causa:</label>
                <input type="number" step="0.01" name="valor_causa" class="form-control" value="<?php echo htmlspecialchars($processo['valor_causa']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor Condenação:</label>
                <input type="number" step="0.01" name="valor_condenacao" class="form-control" value="<?php echo htmlspecialchars($processo['valor_condenacao']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Distribuído em:</label>
                <input type="date" name="distribuido_em" class="form-control" value="<?php echo htmlspecialchars($processo['distribuido_em']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Advogado Responsável:</label>
                <input type="text" class="form-control" name="advogado_responsavel" value="<?php echo htmlspecialchars($processo['advogado_responsavel']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Requerente(s): <small class="text-muted">(um por linha)</small></label>
                <textarea name="requerente" class="form-control" rows="2" required><?php echo htmlspecialchars($processo['requerente']); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Requerido(s): <small class="text-muted">(um por linha)</small></label>
                <textarea name="requerido" class="form-control" rows="2" required><?php echo htmlspecialchars($processo['requerido']); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status:</label>
                <select name="status" class="form-control" required>
                    <option value="Em andamento" <?php if ($processo['status'] == 'Em andamento') echo 'selected'; ?>>Em andamento</option>
                    <option value="Finalizado" <?php if ($processo['status'] == 'Finalizado') echo 'selected'; ?>>Finalizado</option>
                    <option value="Suspenso" <?php if ($processo['status'] == 'Suspenso') echo 'selected'; ?>>Suspenso</option>
                    <option value="Arquivado" <?php if ($processo['status'] == 'Arquivado') echo 'selected'; ?>>Arquivado</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Observações:</label>
                <textarea class="form-control" name="observacoes" rows="3"><?php echo htmlspecialchars($processo['observacoes']); ?></textarea>
            </div>
            <div class="col-12 d-flex flex-column flex-md-row gap-2 mt-3">
                <button type="submit" class="btn btn-bordo w-100 w-md-auto"><i class="fa fa-save me-1"></i>Salvar Alterações</button>
                <a href="visualizar_processo.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 w-md-auto">Cancelar</a>
            </div>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

