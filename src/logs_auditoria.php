<?php
require_once 'config.php';
requireAdmin();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Busca os logs
$query = "SELECT l.*, u.username 
          FROM logs_auditoria l 
          LEFT JOIN usuarios u ON l.usuario_id = u.id 
          ORDER BY l.created_at DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$logs = $stmt->get_result();

// Conta total de logs para paginação
$total_query = "SELECT COUNT(*) as total FROM logs_auditoria";
$total_result = $conn->query($total_query);
$total_logs = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $per_page);

include 'header.php';
?>

<div class="container mt-4">
    <h2>Logs de Auditoria</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Tabela</th>
                    <th>Registro ID</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($log['username'] ?? 'Sistema'); ?></td>
                        <td><?php echo htmlspecialchars($log['acao']); ?></td>
                        <td><?php echo htmlspecialchars($log['tabela_afetada'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($log['registro_id'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Navegação de páginas">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> 