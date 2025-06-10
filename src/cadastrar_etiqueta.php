<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

$nome = trim($_POST['nome'] ?? '');
$cor = $_POST['cor'] ?? '#8e24aa';

if ($nome) {
    $stmt = $pdo->prepare("INSERT INTO etiquetas (nome, cor) VALUES (?, ?)");
    $stmt->execute([$nome, $cor]);
}
header('Location: processos.php');
exit;

