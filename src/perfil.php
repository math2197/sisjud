<?php
require_once 'config.php';
requireLogin();

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $security->validateCSRFToken($_POST['csrf_token'] ?? '');
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_profile':
                    $email = $_POST['email'];
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    
                    // Verifica se o usuário quer alterar a senha
                    if (!empty($new_password)) {
                        // Verifica a senha atual
                        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        
                        if (!$security->verifyPassword($current_password, $user['password'])) {
                            throw new Exception("Senha atual incorreta");
                        }
                        
                        // Atualiza com a nova senha
                        $hashed_password = $security->hashPassword($new_password);
                        $stmt = $conn->prepare("UPDATE usuarios SET email = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $email, $hashed_password, $_SESSION['user_id']);
                    } else {
                        // Atualiza apenas o email
                        $stmt = $conn->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
                        $stmt->bind_param("si", $email, $_SESSION['user_id']);
                    }
                    
                    if ($stmt->execute()) {
                        $security->logAction($_SESSION['user_id'], 'Atualização de perfil', 'usuarios', $_SESSION['user_id']);
                        $mensagem = "Perfil atualizado com sucesso!";
                        $tipo_mensagem = "success";
                    } else {
                        throw new Exception("Erro ao atualizar perfil");
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Busca dados do usuário
$stmt = $conn->prepare("SELECT username, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

include 'header.php';
?>

<div class="container mt-4">
    <h2>Meu Perfil</h2>
    
    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $security->generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group mb-3">
                    <label for="username">Usuário:</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" disabled>
                </div>
                
                <div class="form-group mb-3">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="current_password">Senha Atual:</label>
                    <input type="password" class="form-control" name="current_password" id="current_password">
                    <small class="form-text text-muted">Preencha apenas se desejar alterar a senha</small>
                </div>
                
                <div class="form-group mb-3">
                    <label for="new_password">Nova Senha:</label>
                    <input type="password" class="form-control" name="new_password" id="new_password">
                    <small class="form-text text-muted">Deixe em branco para manter a senha atual</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

