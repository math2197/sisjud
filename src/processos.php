<?php
// Sessão e conexão if (session_status() === PHP_SESSION_NONE) session_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

// Busca com filtro por etiqueta ou outros campos
$busca = $_GET['busca'] ?? '';
$params = [];
$sql = "SELECT DISTINCT p.*, c.nome AS cliente_nome
        FROM processos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN processo_etiqueta pe ON pe.processo_id = p.id
        LEFT JOIN etiquetas e ON e.id = pe.etiqueta_id";
if ($busca) {
    $sql .= " WHERE p.titulo LIKE :busca
           OR p.numero_processo LIKE :busca
           OR p.tipo_acao LIKE :busca
           OR c.nome LIKE :busca
           OR e.nome LIKE :busca";
    $params[':busca'] = "%$busca%";
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$processos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Processos e Casos - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
    .processos-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        margin-left: 180px;
        margin-top: 68px;
        padding-bottom: 30px;
    }
    .btn-fab {
        position: fixed;
        bottom: 32px;
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
    .btn-fab-processo { right: 100px; }
    .btn-fab-etiqueta { right: 32px; background: #ffc107; color: #800020; }
    .btn-fab-etiqueta:hover { background: #e0a800; color: #fff; }
    .card-processo {
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
    .card-processo:hover { box-shadow: 0 6px 24px #0002; }
    .proc-title {
        font-weight: 600;
        font-size: 1.13rem;
        color: #800020;
        margin-bottom: 0.12rem;
        line-height: 1.2;
        text-decoration: none;
        transition: color .2s;
        word-break: break-word;
    }
    .proc-title:hover { color: #a8324a; text-decoration: underline; }
    .proc-sub { font-size: 0.98rem; color: #888; margin-bottom: 0.13rem; }
    .proc-badges { margin-bottom: 0.18rem; }
    .proc-badge {
        background: #f8f9fa;
        color: #800020;
        font-size: 0.86rem;
        border-radius: 5px;
        padding: 0.22em 0.7em;
        margin-right: 0.2em;
        margin-bottom: 2px;
        border: 1px solid #e5e5e5;
        display: inline-block;
    }
    .proc-main { flex-grow: 1; min-width: 0; }
    .proc-col-cliente {
        color: #444;
        font-size: 0.98rem;
        word-break: break-word;
        margin-top: 0.2rem;
    }
    .proc-date-actions {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        min-width: 170px;
        justify-content: flex-end;
    }
    .proc-date {
        color: #888;
        font-size: 0.98rem;
        min-width: 80px;
        text-align: right;
    }
    .proc-actions {
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity .2s;
    }
    .card-processo:hover .proc-actions { opacity: 1; }
    .btn-proc-edit, .btn-proc-etiqueta {
        background: #f8f9fa;
        color: #800020;
        border-radius: 50%;
        border: none;
        font-size: 1.06rem;
        padding: 0.35em 0.49em;
        transition: background .2s, color .2s;
        box-shadow: none;
    }
    .btn-proc-edit:hover, .btn-proc-etiqueta:hover {
        background: #800020;
        color: #fff;
    }
    @media (max-width: 991.98px) {
        .processos-wrapper { margin-left: 0; }
        .proc-date-actions { gap: 0.5rem; min-width: 110px; }
        .card-processo { padding: 0.8rem 0.7rem 0.7rem 1rem; }
        .btn-fab-processo { right: 90px; }
        .btn-fab-etiqueta { right: 24px; }
    }
    @media (max-width: 767.98px) {
        .processos-wrapper { padding: 0 2px; }
        .card-processo { flex-direction: column; align-items: stretch; gap: 0.7rem; }
        .proc-date-actions { justify-content: flex-start; margin-top: 0.5rem; }
        .proc-date { text-align: left; }
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="processos-wrapper main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold" style="font-size:1.18rem;">Processos e casos</h2>
        <form class="d-flex" method="get" action="processos.php">
            <input type="text" class="form-control me-2" name="busca" placeholder="Buscar por título, cliente, ação ou etiqueta..." value="<?php echo htmlspecialchars($busca); ?>" style="min-width:220px;">
            <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <?php foreach ($processos as $proc): ?>
    <div class="card-processo">
        <div class="proc-main">
            <a href="visualizar_processo.php?id=<?php echo $proc['id']; ?>" class="proc-title"><?php echo htmlspecialchars($proc['titulo']); ?></a>
            <div class="proc-sub">
                <?php echo htmlspecialchars($proc['status'] ?? ''); ?>
                <?php if ($proc['numero_processo']): ?>
                    | <?php echo htmlspecialchars($proc['numero_processo']); ?>
                <?php endif; ?>
            </div>
            <div class="proc-badges">
                <?php
                // Exibe etiquetas do processo
                $stmtTag = $pdo->prepare("SELECT e.nome, e.cor FROM etiquetas e
                    JOIN processo_etiqueta pe ON pe.etiqueta_id = e.id
                    WHERE pe.processo_id = ?");
                $stmtTag->execute([$proc['id']]);
                $tags = $stmtTag->fetchAll();
                foreach ($tags as $tag) {
                    echo '<span class="proc-badge" style="background:'.htmlspecialchars($tag['cor']).';color:#fff;margin-right:4px;">'.htmlspecialchars($tag['nome']).'</span>';
                }
                ?>
            </div>
            <div class="proc-col-cliente mt-1">
                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($proc['cliente_nome']); ?>
                <?php if ($proc['advogado_responsavel']): ?>
                    <span class="text-muted small ms-2"><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($proc['advogado_responsavel']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="proc-date-actions">
            <div class="proc-date">
                <?php echo $proc['updated_at'] ? date('d/m/Y', strtotime($proc['updated_at'])) : ''; ?>
            </div>
            <div class="proc-actions">
                <a href="editar_processo.php?id=<?php echo $proc['id']; ?>" class="btn-proc-edit" title="Editar"><i class="fa fa-edit"></i></a>
                <button type="button"
                        class="btn-proc-etiqueta"
                        title="Etiquetas"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEtiquetas"
                        data-processo="<?php echo $proc['id']; ?>">
                    <i class="fa-solid fa-tags"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($processos)): ?>
    <div class="text-center text-muted py-4">Nenhum processo encontrado.</div>
    <?php endif; ?>
</div>

<!-- Botão flutuante para abrir o modal de novo processo -->
<button type="button" class="btn btn-primary btn-fab btn-fab-processo"
        data-bs-toggle="modal" data-bs-target="#modalNovoProcesso" title="Cadastrar novo processo">
    <i class="fa fa-plus"></i>
</button>

<!-- Botão flutuante para abrir o modal de nova etiqueta -->
<button type="button" class="btn btn-warning btn-fab btn-fab-etiqueta"
        data-bs-toggle="modal" data-bs-target="#modalNovaEtiqueta" title="Criar nova etiqueta">
    <i class="fa fa-tag"></i>
</button>

<!-- Modal de Cadastro de Processo -->
<div class="modal fade" id="modalNovoProcesso" tabindex="-1" aria-labelledby="modalNovoProcessoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="cadastrar_processo.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalNovoProcessoLabel" style="color:#800020;">Cadastrar Novo Processo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Número do Processo:</label>
            <input type="text" name="numero_processo" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Cliente:</label>
            <select name="cliente_id" class="form-control" required>
              <option value="">Selecione...</option>
              <?php
              $clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();
              foreach ($clientes as $cli): ?>
                <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nome']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de Ação:</label>
            <input type="text" name="tipo_acao" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Objeto:</label>
            <textarea name="objeto" class="form-control" required></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Juízo:</label>
            <input type="text" name="juizo" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Foro:</label>
            <input type="text" name="foro" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Link no Tribunal:</label>
            <input type="url" name="link_tribunal" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Valor da Causa:</label>
            <input type="number" step="0.01" name="valor_causa" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Valor Condenação:</label>
            <input type="number" step="0.01" name="valor_condenacao" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Distribuído em:</label>
            <input type="date" name="distribuido_em" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Advogado Responsável:</label>
            <input type="text" class="form-control" name="advogado_responsavel">
          </div>
          <div class="col-md-6">
            <label class="form-label">Requerente(s): <small class="text-muted">(um por linha)</small></label>
            <textarea name="requerente" class="form-control" rows="2" required></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Requerido(s): <small class="text-muted">(um por linha)</small></label>
            <textarea name="requerido" class="form-control" rows="2" required></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status:</label>
            <select name="status" class="form-control" required>
              <option value="Em andamento">Em andamento</option>
              <option value="Finalizado">Finalizado</option>
              <option value="Suspenso">Suspenso</option>
              <option value="Arquivado">Arquivado</option>
            </select>
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

<!-- Modal de Cadastro de Etiqueta -->
<div class="modal fade" id="modalNovaEtiqueta" tabindex="-1" aria-labelledby="modalNovaEtiquetaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="cadastrar_etiqueta.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalNovaEtiquetaLabel" style="color:#800020;">Cadastrar Nova Etiqueta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nome da Etiqueta:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Cor:</label>
            <input type="color" name="cor" class="form-control form-control-color" value="#8e24aa" title="Escolha uma cor">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-bordo" type="submit">Cadastrar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de Etiquetas do Processo -->
<div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="atribuir_etiquetas.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEtiquetasLabel">Etiquetas do Processo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="processo_id" id="etiquetaProcessoId" value="">
        <?php
        $allTags = $pdo->query("SELECT * FROM etiquetas ORDER BY nome")->fetchAll();
        foreach ($allTags as $tag): ?>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="etiquetas[]" id="tag_<?php echo $tag['id']; ?>" value="<?php echo $tag['id']; ?>">
                <label class="form-check-label" for="tag_<?php echo $tag['id']; ?>" style="background:<?php echo htmlspecialchars($tag['cor']); ?>;color:#fff;padding:2px 8px;border-radius:6px;">
                    <?php echo htmlspecialchars($tag['nome']); ?>
                </label>
            </div>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button class="btn btn-bordo" type="submit">Salvar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<script>
document.querySelectorAll('.btn-proc-etiqueta').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('etiquetaProcessoId').value = this.getAttribute('data-processo');
        // Opcional: AJAX para marcar as etiquetas já atribuídas
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

