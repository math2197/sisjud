<?php
class Security {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function logAction($usuario_id, $acao, $tabela_afetada = null, $registro_id = null, $dados_anteriores = null, $dados_novos = null) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $stmt = $this->conn->prepare("
            INSERT INTO logs_auditoria 
            (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isssssss", 
            $usuario_id, 
            $acao, 
            $tabela_afetada, 
            $registro_id, 
            $dados_anteriores, 
            $dados_novos, 
            $ip, 
            $user_agent
        );
        
        return $stmt->execute();
    }
    
    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token validation failed');
        }
        return true;
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function regenerateSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
    
    public function setSecureSession() {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
    }
} 