<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Inclui conexão para buscar foto do usuário
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$foto_url = 'avatar.png';
if ($user_id) {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $foto = $stmt->fetchColumn();
    if (!empty($foto) && file_exists(__DIR__ . '/' . $foto)) {
        $foto_url = $foto;
    }
}
?>

<style>
.header {
    height: 68px;
    background: #f8f9fa;
    border-bottom: 1.5px solid #eee;
    position: fixed;
    top: 0;
    left: 180px; /* largura do sidebar */
    right: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 32px;
    box-shadow: 0 2px 8px #0001;
}
.header .dropdown-toggle {
    cursor: pointer;
    display: flex;
    align-items: center;
    color: #800020;
    font-weight: bold;
    text-decoration: none;
    gap: 8px;
}
.header .dropdown-toggle img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 2px solid #800020;
    background: #fff;
    object-fit: cover;
}
#buscaGlobalForm {
    width: 300px;
    margin-right: auto;
}
#buscaGlobal {
    width: 100%;
    border-radius: 20px;
    padding-left: 15px;
    background: #fff;
    border: 1px solid #ccc;
}
#resultadosBusca {
    max-height: 300px;
    overflow-y: auto;
}
</style>

<div class="header">
    <form class="d-inline-block position-relative me-3" id="buscaGlobalForm" autocomplete="off">
      <input type="text" id="buscaGlobal" class="form-control" placeholder="Buscar...">
      <div id="resultadosBusca" class="list-group position-absolute w-100" style="z-index:999;"></div>
    </form>
    <div class="dropdown">
        <a href="#" class="dropdown-toggle" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <img src="<?php echo htmlspecialchars($foto_url); ?>" alt="Avatar">
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="perfilDropdown">
            <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
            <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
                <li><a class="dropdown-item" href="usuarios.php">Usuários</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscaInput = document.getElementById('buscaGlobal');
    const resultadosDiv = document.getElementById('resultadosBusca');

    buscaInput.addEventListener('input', function() {
        let termo = this.value;
        if (termo.length < 2) {
            resultadosDiv.innerHTML = '';
            return;
        }
        fetch('busca_global.php?q=' + encodeURIComponent(termo))
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(item => {
                    let link = '#';
                    if (item.tipo === 'processo') link = 'visualizar_processo.php?id=' + item.id;
                    if (item.tipo === 'cliente') link = 'clientes.php?busca=' + encodeURIComponent(item.nome);
                    if (item.tipo === 'tarefa') link = 'visualizar_processo.php?id=' + (item.processo_id ?? '#');
                    html += `<a href="${link}" class="list-group-item list-group-item-action">
                        <strong>${item.nome}</strong> <span class="badge bg-secondary">${item.tipo}</span>
                    </a>`;
                });
                resultadosDiv.innerHTML = html;
            });
    });

    // Esconde resultados ao clicar fora
    document.addEventListener('click', function(e) {
        if (!document.getElementById('buscaGlobalForm').contains(e.target)) {
            resultadosDiv.innerHTML = '';
        }
    });
});
</script>

