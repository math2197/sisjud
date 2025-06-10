<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['perfil'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Cadastro de novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $senha = md5($_POST['senha']);
    $perfil = $_POST['perfil'];
    $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, email, perfil, precisa_alterar_senha) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$username, $senha, $email, $perfil]);
    header("Location: usuarios.php");
    exit;
}

// Listagem de usuários
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY username")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Usuários - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2 class="mb-4">Usuários</h2>
    <form method="POST" class="row g-2 mb-4">
        <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Usuário" required></div>
        <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="E-mail" required></div>
        <div class="col-md-3"><input type="password" name="senha" class="form-control" placeholder="Senha" required></div>
        <div class="col-md-2">
            <select name="perfil" class="form-control">
                <option value="usuario">Usuário</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-bordo w-100" type="submit">Cadastrar</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Usuário</th><th>E-mail</th><th>Perfil</th><th>Primeiro acesso?</th></tr></thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['perfil']); ?></td>
                    <td><?php echo $u['precisa_alterar_senha'] ? 'Sim' : 'Não'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

