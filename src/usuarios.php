<?php
require_once 'config.php';
requireAdmin();

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $security->validateCSRFToken($_POST['csrf_token'] ?? '');
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $username = $_POST['username'];
                    $password = $_POST['password'];
                    $email = $_POST['email'];
                    $perfil = $_POST['perfil'];
                    
                    $hashed_password = $security->hashPassword($password);
                    
                    $stmt = $conn->prepare("INSERT INTO usuarios (username, password, email, perfil) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $hashed_password, $email, $perfil);
                    
                    if ($stmt->execute()) {
                        $security->logAction($_SESSION['user_id'], 'Criação de usuário', 'usuarios', $conn->insert_id);
                        $mensagem = "Usuário criado com sucesso!";
                        $tipo_mensagem = "success";
                    } else {
                        throw new Exception("Erro ao criar usuário");
                    }
                    break;
                    
                case 'update':
                    $id = $_POST['id'];
                    $email = $_POST['email'];
                    $perfil = $_POST['perfil'];
                    
                    $stmt = $conn->prepare("UPDATE usuarios SET email = ?, perfil = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $email, $perfil, $id);
                    
                    if ($stmt->execute()) {
                        $security->logAction($_SESSION['user_id'], 'Atualização de usuário', 'usuarios', $id);
                        $mensagem = "Usuário atualizado com sucesso!";
                        $tipo_mensagem = "success";
                    } else {
                        throw new Exception("Erro ao atualizar usuário");
                    }
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    
                    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $security->logAction($_SESSION['user_id'], 'Exclusão de usuário', 'usuarios', $id);
                        $mensagem = "Usuário excluído com sucesso!";
                        $tipo_mensagem = "success";
                    } else {
                        throw new Exception("Erro ao excluir usuário");
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Busca todos os usuários
$usuarios = $conn->query("SELECT id, username, email, perfil, created_at FROM usuarios ORDER BY username");

include 'header.php';
?>

<div class="container mt-4">
    <h2>Gerenciamento de Usuários</h2>
    
    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Novo Usuário</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $security->generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="username">Usuário:</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="password">Senha:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="perfil">Perfil:</label>
                            <select class="form-control" name="perfil" required>
                                <option value="usuario">Usuário</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Criar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($usuario['perfil'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="excluirUsuario(<?php echo $usuario['id']; ?>)">
                                Excluir
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuário</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label>Usuário:</label>
                        <input type="text" class="form-control" id="edit_username" disabled>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email:</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_perfil">Perfil:</label>
                        <select class="form-control" name="perfil" id="edit_perfil" required>
                            <option value="usuario">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="excluirModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este usuário?</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $security->generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editarUsuario(usuario) {
    document.getElementById('edit_id').value = usuario.id;
    document.getElementById('edit_username').value = usuario.username;
    document.getElementById('edit_email').value = usuario.email;
    document.getElementById('edit_perfil').value = usuario.perfil;
    $('#editarModal').modal('show');
}

function excluirUsuario(id) {
    document.getElementById('delete_id').value = id;
    $('#excluirModal').modal('show');
}
</script>

<?php include 'footer.php'; ?>

