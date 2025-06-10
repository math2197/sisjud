<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID de processo inválido.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_andamento = $_POST['data_andamento'];
    $descricao = $_POST['descricao'];
    $stmt = $pdo->prepare("INSERT INTO andamentos (processo_id, data_andamento, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$id, $data_andamento, $descricao]);
    header("Location: visualizar_processo.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Andamento - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2>Novo Andamento</h2>
    <form method="POST" class="mt-4 col-md-6">
        <div class="mb-3">
            <label for="data_andamento" class="form-label">Data do Andamento:</label>
            <input type="date" class="form-control" name="data_andamento" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição:</label>
            <textarea class="form-control" name="descricao" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-bordo">Salvar Andamento</button>
        <a href="visualizar_processo.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>

