<?php
require_once 'config.php';
$termo = $_GET['q'] ?? '';
$resultados = [];
if ($termo) {
    // Processos
    $stmt = $pdo->prepare("SELECT id, numero_processo as nome, 'processo' as tipo FROM processos WHERE numero_processo LIKE ? LIMIT 5");
    $stmt->execute(['%'.$termo.'%']);
    $resultados = array_merge($resultados, $stmt->fetchAll(PDO::FETCH_ASSOC));
    // Clientes
    $stmt = $pdo->prepare("SELECT id, nome, 'cliente' as tipo FROM clientes WHERE nome LIKE ? LIMIT 5");
    $stmt->execute(['%'.$termo.'%']);
    $resultados = array_merge($resultados, $stmt->fetchAll(PDO::FETCH_ASSOC));
    // Tarefas
    $stmt = $pdo->prepare("SELECT id, titulo as nome, 'tarefa' as tipo FROM tarefas WHERE titulo LIKE ? LIMIT 5");
    $stmt->execute(['%'.$termo.'%']);
    $resultados = array_merge($resultados, $stmt->fetchAll(PDO::FETCH_ASSOC));
}
header('Content-Type: application/json');
echo json_encode($resultados);

