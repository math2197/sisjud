<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

// Cadastrar nova etiqueta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = trim($_POST['nome']);
    $cor = $_POST['cor'] ?? '#8e24aa';
    if ($nome) {
        $stmt = $pdo->prepare("INSERT INTO etiquetas (nome, cor) VALUES (?, ?)");
        $stmt->execute([$nome, $cor]);
        header('Location: etiquetas.php');
        exit;
    }
}

// Excluir etiqueta
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $pdo->prepare("DELETE FROM etiquetas WHERE id=?")->execute([$id]);
    header('Location: etiquetas.php');
    exit;
}

// Listar etiquetas
$etiquetas = $pdo->query("SELECT * FROM etiquetas ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Etiquetas - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    .badge-label { display:inline-block; min-width:64px; text-align:center; font-size:0.98rem; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="container main-content" style="max-width:600px;">
    <h3 class="mb-4">Gerenciar Etiquetas</h3>
    <form method="POST" class="row g-2 mb-4">
        <div class="col-6">
            <input type="text" name="nome" class="form-control" placeholder="Nome da etiqueta" required>
        </div>
        <div class="col-4">
            <input type="color" name="cor" class="form-control form-control-color" value="#8e24aa" title="Escolha uma cor">
        </div>
        <div class="col-2">
            <button class="btn btn-bordo w-100" type="submit"><i class="fa fa-plus"></i> Adicionar</button>
        </div>
    </form>
    <table class="table table-striped">
        <thead><tr><th>Etiqueta</th><th>Cor</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($etiquetas as $et): ?>
            <tr>
                <td><?php echo htmlspecialchars($et['nome']); ?></td>
                <td><span class="badge-label" style="background:<?php echo htmlspecialchars($et['cor']); ?>;color:#fff;"><?php echo htmlspecialchars($et['nome']); ?></span></td>
                <td>
                    <a href="?excluir=<?php echo $et['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir esta etiqueta?');"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

