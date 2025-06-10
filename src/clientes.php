<?php
// Sessão e conexão if (session_status() === PHP_SESSION_NONE) session_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

// Busca clientes (com filtro de busca)
$busca = $_GET['busca'] ?? '';
$params = [];
$sql = "SELECT * FROM clientes";
if ($busca) {
    $sql .= " WHERE nome LIKE :busca OR cpf_cnpj LIKE :busca OR contato LIKE :busca OR email LIKE :busca";
    $params[':busca'] = "%$busca%";
}
$sql .= " ORDER BY nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Clientes - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
    .clientes-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        margin-left: 180px;
        margin-top: 68px;
        padding-bottom: 30px;
    }
    .card-cliente {
        border-radius: 18px;
        box-shadow: 0 2px 12px #0001;
        margin-bottom: 1.1rem;
        border: none;
        padding: 1.1rem 1.6rem 0.9rem 1.6rem;
        background: #fff;
        transition: box-shadow .2s;
        display: flex;
        align-items: center;
        width: 100%;
        position: relative;
    }
    .card-cliente:hover { box-shadow: 0 6px 24px #0002; }
    .cliente-main { flex-grow: 1; min-width: 0; }
    .cliente-nome {
        font-weight: 600;
        font-size: 1.13rem;
        color: #800020;
        margin-bottom: 0.12rem;
        line-height: 1.2;
        word-break: break-word;
    }
    .cliente-info { font-size: 0.98rem; color: #888; margin-bottom: 0.13rem; }
    .cliente-contato { color: #444; font-size: 0.97rem; word-break: break-word; }
    .cliente-actions {
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity .2s;
    }
    .card-cliente:hover .cliente-actions { opacity: 1; }
    .btn-cliente-edit, .btn-cliente-del {
        background: #f8f9fa;
        color: #800020;
        border-radius: 50%;
        border: none;
        font-size: 1.08rem;
        padding: 0.38em 0.52em;
        transition: background .2s, color .2s;
        box-shadow: none;
    }
    .btn-cliente-edit:hover {
        background: #800020;
        color: #fff;
    }
    .btn-cliente-del:hover {
        background: #dc3545;
        color: #fff;
    }
    .btn-fab {
        position: fixed;
        bottom: 32px;
        right: 32px;
        z-index: 1050;
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
        border-radius: 50%;
        box-shadow: 0 2px 8px #0002;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (max-width: 991.98px) {
        .clientes-wrapper { margin-left: 0; }
        .card-cliente { padding: 0.8rem 0.7rem 0.7rem 1rem; }
    }
    @media (max-width: 767.98px) {
        .clientes-wrapper { padding: 0 2px; }
        .card-cliente { flex-direction: column; align-items: stretch; gap: 0.7rem; }
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="clientes-wrapper main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold" style="font-size:1.18rem;">Clientes</h2>
        <form class="d-flex" method="get" action="clientes.php">
            <input type="text" class="form-control me-2" name="busca" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($busca); ?>" style="min-width:200px;">
            <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <?php foreach ($clientes as $cli): ?>
    <div class="card-cliente">
        <div class="cliente-main">
            <div class="cliente-nome"><?php echo htmlspecialchars($cli['nome']); ?></div>
            <div class="cliente-info">
                <?php if ($cli['cpf_cnpj']): ?>
                    <span><i class="fa-regular fa-id-card"></i> <?php echo htmlspecialchars($cli['cpf_cnpj']); ?></span>
                <?php endif; ?>
                <?php if ($cli['email']): ?>
                    <span class="ms-3"><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($cli['email']); ?></span>
                <?php endif; ?>
            </div>
            <div class="cliente-contato">
                <?php if ($cli['contato']): ?>
                    <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($cli['contato']); ?>
                <?php endif; ?>
                <?php if ($cli['endereco']): ?>
                    <span class="ms-3"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($cli['endereco']); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($cli['observacoes']): ?>
                <div class="text-muted mt-1" style="font-size:0.96rem;"><?php echo nl2br(htmlspecialchars($cli['observacoes'])); ?></div>
            <?php endif; ?>
        </div>
        <div class="cliente-actions">
            <a href="editar_cliente.php?id=<?php echo $cli['id']; ?>" class="btn-cliente-edit" title="Editar"><i class="fa fa-edit"></i></a>
            <a href="excluir_cliente.php?id=<?php echo $cli['id']; ?>" class="btn-cliente-del" title="Excluir" onclick="return confirm('Deseja realmente excluir este cliente?');"><i class="fa fa-trash"></i></a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($clientes)): ?>
    <div class="text-center text-muted py-4">Nenhum cliente encontrado.</div>
    <?php endif; ?>
</div>

<!-- Botão flutuante para abrir o modal -->
<button type="button" class="btn btn-primary btn-fab"
        data-bs-toggle="modal" data-bs-target="#modalNovoCliente" title="Cadastrar novo cliente">
    <i class="fa fa-plus"></i>
</button>

<!-- Modal de Cadastro de Cliente -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-labelledby="modalNovoClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="cadastrar_cliente.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalNovoClienteLabel" style="color:#800020;">Cadastrar Novo Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">CPF/CNPJ:</label>
            <input type="text" name="cpf_cnpj" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Contato:</label>
            <input type="text" name="contato" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">E-mail:</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Endereço:</label>
            <input type="text" name="endereco" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Observações:</label>
            <textarea class="form-control" name="observacoes" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-bordo"><i class="fa fa-save me-1"></i>Cadastrar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

