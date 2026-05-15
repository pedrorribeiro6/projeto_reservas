<?php
session_start();

// Verifica se o usuário tem permissão para acessar a rota
function proteger_pagina($tipo_permitido = null) {
    // Se não estiver logado, joga para o painel de seleção de acesso
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php");
        exit();
    }

    // Se a rota exige um tipo específico (ex: professor) e o logado não é, bloqueia.
    // O Administrador normalmente tem acesso a tudo, então abrimos uma exceção ou o tratamos de forma isolada.
    if ($tipo_permitido) {
        if ($_SESSION['tipo_conta'] !== $tipo_permitido && $_SESSION['tipo_conta'] !== 'admin') {
            header("Location: index.php");
            exit();
        }
    }
}

// Verifica se o usuário atual é admin
function is_admin() {
    return isset($_SESSION['tipo_conta']) && $_SESSION['tipo_conta'] === 'admin';
}
?>
