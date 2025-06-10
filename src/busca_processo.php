<?php
require_once 'config.php';
$termo = $_GET['termo'] ?? '';
$stmt = $pdo->prepare("SELECT id, numero_processo FROM processos WHERE numero_processo LIKE ? ORDER BY numero_processo LIMIT 10");
$stmt->execute(['%' . $termo . '%']);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

