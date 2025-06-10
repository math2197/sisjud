<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<style>
.sidebar {
    width: 180px; /* largura reduzida */
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #800020 90%, #a8324a 100%);
    color: #fff;
    display: flex;
    flex-direction: column;
    z-index: 100;
    padding-top: 20px;
    box-shadow: 2px 0 8px #0001;
}
.sidebar .logo-sl {
    font-weight: bold;
    font-size: 1.3rem; /* menor */
    color: #fff;
    letter-spacing: 2px;
    margin: 0 auto 22px auto;
    text-align: center;
}
.sidebar .sidebar-menu {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.sidebar .nav-link {
    color: #fff;
    padding: 9px 16px; /* mais compacto */
    font-size: 0.98rem; /* menor */
    text-decoration: none;
    border-radius: 0 20px 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 2px 0;
    transition: background 0.2s, color 0.2s;
}
.sidebar .nav-link:hover, .sidebar .nav-link.active {
    background: #fff2;
    color: #ffc107;
    font-weight: bold;
    border-left: 5px solid #ffc107;
}
.sidebar .sidebar-footer {
    margin-top: auto;
    border-top: 1px solid #fff2;
    padding-top: 14px;
    padding-bottom: 14px;
    text-align: center;
}
.sidebar .sidebar-footer .nav-link {
    color: #fff;
    font-size: 0.93rem; /* menor */
    border-radius: 10px;
    justify-content: center;
}
</style>
<div class="sidebar">
    <div class="logo-sl"><i class="fa-solid fa-scale-balanced"></i> SL Advocacia</div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo ' active'; ?>">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        <a href="processos.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='processos.php') echo ' active'; ?>">
            <i class="fa-solid fa-scale-balanced"></i> Processos
        </a>
        <a href="clientes.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='clientes.php') echo ' active'; ?>">
            <i class="fa-solid fa-user"></i> Clientes
        </a>
        <a href="relatorios.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='relatorios.php') echo ' active'; ?>">
            <i class="fa-solid fa-file-lines"></i> Relatórios
        </a>
    </div>
</div>

