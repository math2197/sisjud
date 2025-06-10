<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

$processo_id = intval($_POST['processo_id'] ?? 0);
$etiquetas = $_POST['etiquetas'] ?? [];

if ($processo_id > 0) {
    $pdo->prepare("DELETE FROM processo_etiqueta WHERE processo_id=?")->execute([$processo_id]);
    foreach ($etiquetas as $etiqueta_id) {
        $pdo->prepare("INSERT INTO processo_etiqueta (processo_id, etiqueta_id) VALUES (?, ?)")->execute([$processo_id, $etiqueta_id]);
    }
}
header('Location: processos.php');
exit;

