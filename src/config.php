<?php
require_once __DIR__ . '/../php/config/Security.php';

// Configura as opções de sessão antes de iniciá-la
Security::configureSession();
session_start();

// Configurações do banco de dados
$host = 'mysql';
$dbname = 'advocacia_db';
$username = 'root';
$password = 'root123';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Inicializa o objeto de segurança
$security = new Security($conn);

// Regenera o ID da sessão a cada 30 minutos
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 1800) {
    $security->regenerateSession();
    $_SESSION['last_regeneration'] = time();
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para verificar se o usuário é admin
function isAdmin() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin';
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Função para redirecionar se não for admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: dashboard.php");
        exit();
    }
}

?>
