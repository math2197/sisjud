<?php
$host = 'mysql';
$dbname = 'advocacia_db';
$username = 'advocacia_user';
$password = 'advocacia_pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

?>
