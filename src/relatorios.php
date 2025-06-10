<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios - SL Advocacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>
<div class="main-content">
    <h2>Relatórios de Processos</h2>
    <form method="get" class="row g-2 align-items-end mb-4" id="formRelatorio">
      <div class="col-auto">
        <label for="data_inicio" class="form-label mb-0">De:</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
      </div>
      <div class="col-auto">
        <label for="data_fim" class="form-label mb-0">Até:</label>
        <input type="date" name="data_fim" id="data_fim" class="form-control" required>
      </div>
      <div class="col-auto d-flex gap-2">
        <button type="submit" formaction="relatorio_processos_pdf.php" formtarget="_blank" class="btn btn-danger">
          <i class="fa fa-file-pdf"></i> Gerar PDF
        </button>
        <button type="submit" formaction="relatorio_processos_excel.php" formtarget="_blank" class="btn btn-success">
          <i class="fa fa-file-excel"></i> Gerar Excel
        </button>
      </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

