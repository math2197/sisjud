<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_senha = '';

// Diretório para salvar as fotos de perfil
$upload_dir = __DIR__ . '/uploads/perfis/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

// Upload da foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto'])) {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nome_arquivo = basename($_FILES['foto']['name']);
        $ext = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $ext_permitidas)) {
            $msg = '<div class="alert alert-danger">Formato de arquivo não permitido. Use JPG, PNG ou GIF.</div>';
        } else {
            $novo_nome = 'user_' . $user_id . '.' . $ext;
            $destino = $upload_dir . $novo_nome;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->execute(['uploads/perfis/' . $novo_nome, $user_id]);
                $msg = '<div class="alert alert-success">Foto de perfil atualizada com sucesso!</div>';
            } else {
                $msg = '<div class="alert alert-danger">Erro ao salvar a foto.</div>';
            }
        }
    } else {
        $msg = '<div class="alert alert-danger">Selecione uma foto válida.</div>';
    }
}

// Alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    // Busca senha atual
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $hash_atual = $stmt->fetchColumn();

    // Supondo que você usa password_hash/password_verify (recomendado)
    if (!password_verify($senha_atual, $hash_atual)) {
        $msg_senha = '<div class="alert alert-danger">Senha atual incorreta.</div>';
    } elseif (strlen($nova_senha) < 6) {
        $msg_senha = '<div class="alert alert-danger">A nova senha deve ter pelo menos 6 caracteres.</div>';
    } elseif ($nova_senha !== $confirma_senha) {
        $msg_senha = '<div class="alert alert-danger">As senhas não coincidem.</div>';
    } else {
        $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->execute([$nova_hash, $user_id]);
        $msg_senha = '<div class="alert alert-success">Senha alterada com sucesso!</div>';
    }
}

// Busca dados do usuário
$stmt = $pdo->prepare("SELECT username, email, foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2>Meu Perfil</h2>
    <?php echo $msg; ?>
    <div class="card p-4" style="max-width: 400px;">
        <div class="text-center mb-3">
            <?php if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/' . $usuario['foto_perfil'])): ?>
                <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de Perfil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <img src="avatar.png" alt="Foto de Perfil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="foto" class="form-label">Alterar Foto de Perfil</label>
                <input type="file" name="foto" id="foto" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" name="upload_foto" class="btn btn-bordo">Enviar</button>
        </form>
        <hr>
        <p><strong>Usuário:</strong> <?php echo htmlspecialchars($usuario['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
    </div>
    <div class="card p-4 mt-4" style="max-width: 400px;">
        <h5>Alterar Senha</h5>
        <?php echo $msg_senha; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="senha_atual" class="form-label">Senha Atual</label>
                <input type="password" name="senha_atual" id="senha_atual" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="nova_senha" class="form-label">Nova Senha</label>
                <input type="password" name="nova_senha" id="nova_senha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirma_senha" class="form-label">Confirme a Nova Senha</label>
                <input type="password" name="confirma_senha" id="confirma_senha" class="form-control" required>
            </div>
            <button type="submit" name="alterar_senha" class="btn btn-bordo">Alterar Senha</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

