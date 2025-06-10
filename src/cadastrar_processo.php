<?php
require_once 'config.php';

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

    // Validação básica
    if (!$titulo || !$numero_processo || !$cliente_id || !$tipo_acao || !$objeto || !$requerente || !$requerido) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO processos
                (numero_processo, titulo, cliente_id, tipo_acao, objeto, status, juizo, foro, link_tribunal, valor_causa, valor_condenacao, distribuido_em, requerente, requerido, observacoes, advogado_responsavel)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $numero_processo, $titulo, $cliente_id, $tipo_acao, $objeto, $status, $juizo, $foro, $link_tribunal,
                $valor_causa, $valor_condenacao, $distribuido_em, $requerente, $requerido, $observacoes, $advogado_responsavel
            ]);
            header('Location: processos.php?cadastro=ok');
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar processo: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastrar Processo - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2 class="mb-4">Cadastrar Novo Processo</h2>
    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>
    <form method="POST" class="card p-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Título:</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Número do Processo:</label>
                <input type="text" name="numero_processo" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Cliente:</label>
            <select name="cliente_id" class="form-control" required>
                <option value="">Selecione...</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tipo de Ação:</label>
                <input type="text" name="tipo_acao" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Objeto:</label>
                <textarea name="objeto" class="form-control" required></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Juízo:</label>
                <input type="text" name="juizo" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Foro:</label>
                <input type="text" name="foro" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Link no Tribunal:</label>
                <input type="url" name="link_tribunal" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Valor da Causa:</label>
                <input type="number" step="0.01" name="valor_causa" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Valor Condenação:</label>
                <input type="number" step="0.01" name="valor_condenacao" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Distribuído em:</label>
                <input type="date" name="distribuido_em" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Advogado Responsável:</label>
                <input type="text" class="form-control" name="advogado_responsavel">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Requerente(s): <small class="text-muted">(um por linha)</small></label>
                <textarea name="requerente" class="form-control" rows="2" required></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Requerido(s): <small class="text-muted">(um por linha)</small></label>
                <textarea name="requerido" class="form-control" rows="2" required></textarea>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Status:</label>
            <select name="status" class="form-control" required>
                <option value="Em andamento">Em andamento</option>
                <option value="Finalizado">Finalizado</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Arquivado">Arquivado</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Observações:</label>
            <textarea class="form-control" name="observacoes" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-bordo">Cadastrar</button>
        <a href="processos.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

