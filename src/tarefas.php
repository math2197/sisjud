<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Exclusão de tarefa
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id=?");
    $stmt->execute([$id]);
    header("Location: tarefas.php");
    exit;
}

// Busca avançada
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
if ($busca !== '') {
    $stmt = $pdo->prepare("SELECT t.*, p.numero_processo FROM tarefas t LEFT JOIN processos p ON t.processo_id=p.id WHERE t.titulo LIKE ? OR t.descricao LIKE ? OR p.numero_processo LIKE ? ORDER BY t.data_prazo ASC");
    $param = "%$busca%";
    $stmt->execute([$param, $param, $param]);
    $tarefas = $stmt->fetchAll();
} else {
    $tarefas = $pdo->query("SELECT t.*, p.numero_processo FROM tarefas t LEFT JOIN processos p ON t.processo_id=p.id ORDER BY t.data_prazo ASC")->fetchAll();
}

// Cadastro de nova tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo']) && !isset($_POST['editar_id'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_prazo = $_POST['data_prazo'];
    $status = $_POST['status'];
    $processo_id = $_POST['processo_id'] ?: null;
    $usuario = $_SESSION['username'];
    $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, data_prazo, status, processo_id, usuario) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descricao, $data_prazo, $status, $processo_id, $usuario]);
    header("Location: tarefas.php");
    exit;
}

// Edição de tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id = $_POST['editar_id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_prazo = $_POST['data_prazo'];
    $status = $_POST['status'];
    $processo_id = $_POST['processo_id'] ?: null;
    $stmt = $pdo->prepare("UPDATE tarefas SET titulo=?, descricao=?, data_prazo=?, status=?, processo_id=? WHERE id=?");
    $stmt->execute([$titulo, $descricao, $data_prazo, $status, $processo_id, $id]);
    header("Location: tarefas.php");
    exit;
}

// Se edição, carrega tarefa
$editar = null;
if (isset($_GET['editar'])) {
    $editar_id = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT t.*, p.numero_processo FROM tarefas t LEFT JOIN processos p ON t.processo_id=p.id WHERE t.id=?");
    $stmt->execute([$editar_id]);
    $editar = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tarefas - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
    #sugestoes_processo {
        position: absolute;
        z-index: 1000;
        width: 100%;
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2 class="mb-4">Tarefas</h2>
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por título, descrição ou número do processo" value="<?php echo htmlspecialchars($busca); ?>">
            <button class="btn btn-bordo" type="submit">Buscar</button>
        </div>
    </form>
    <div class="mb-4">
        <form method="POST" class="row g-2 align-items-end position-relative">
            <?php if ($editar): ?>
                <input type="hidden" name="editar_id" value="<?php echo $editar['id']; ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <input type="text" name="titulo" class="form-control" placeholder="Título" required value="<?php echo $editar['titulo'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="data_prazo" class="form-control" required value="<?php echo $editar['data_prazo'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="Pendente" <?php if(($editar['status'] ?? '')=='Pendente') echo 'selected'; ?>>Pendente</option>
                    <option value="Concluída" <?php if(($editar['status'] ?? '')=='Concluída') echo 'selected'; ?>>Concluída</option>
                </select>
            </div>
            <div class="col-md-3 position-relative">
                <input type="text" id="busca_processo" class="form-control" placeholder="Número do Processo" autocomplete="off" value="<?php echo $editar ? htmlspecialchars($editar['numero_processo'] ?? '') : ''; ?>">
                <input type="hidden" name="processo_id" id="processo_id" value="<?php echo $editar['processo_id'] ?? ''; ?>">
                <div id="sugestoes_processo" class="list-group"></div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-bordo w-100" type="submit"><?php echo $editar ? 'Salvar Alterações' : 'Cadastrar Tarefa'; ?></button>
                <?php if ($editar): ?>
                    <a href="tarefas.php" class="btn btn-secondary w-100 mt-1">Cancelar</a>
                <?php endif; ?>
            </div>
            <div class="col-md-12 mt-2">
                <textarea name="descricao" class="form-control" placeholder="Descrição"><?php echo $editar['descricao'] ?? ''; ?></textarea>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Prazo</th>
                    <th>Status</th>
                    <th>Processo</th>
                    <th>Usuário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tarefas as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['titulo']); ?></td>
                    <td><?php echo $t['data_prazo'] ? date('d/m/Y', strtotime($t['data_prazo'])) : ''; ?></td>
                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                    <td><?php echo htmlspecialchars($t['numero_processo']); ?></td>
                    <td><?php echo htmlspecialchars($t['usuario']); ?></td>
                    <td>
                        <a href="tarefas.php?editar=<?php echo $t['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                        <a href="tarefas.php?excluir=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir esta tarefa?');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tarefas)): ?>
                <tr><td colspan="6" class="text-center">Nenhuma tarefa cadastrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var buscaInput = document.getElementById('busca_processo');
    var sugestoesDiv = document.getElementById('sugestoes_processo');
    var processoIdInput = document.getElementById('processo_id');

    buscaInput.addEventListener('input', function() {
        var termo = this.value;
        if (termo.length < 3) {
            sugestoesDiv.innerHTML = '';
            return;
        }
        fetch('busca_processo.php?termo=' + encodeURIComponent(termo))
            .then(response => response.json())
            .then(data => {
                let sugestoes = '';
                data.forEach(function(processo) {
                    sugestoes += `<a href="#" class="list-group-item list-group-item-action" onclick="selecionarProcesso('${processo.id}', '${processo.numero_processo}');return false;">${processo.numero_processo}</a>`;
                });
                sugestoesDiv.innerHTML = sugestoes;
            });
    });

    window.selecionarProcesso = function(id, numero) {
        processoIdInput.value = id;
        buscaInput.value = numero;
        sugestoesDiv.innerHTML = '';
    };
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

