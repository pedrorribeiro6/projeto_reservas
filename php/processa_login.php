<?php
session_start();
require 'conexao.php';

// O usuário Professor Teste foi removido permanentemente.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $credencial = $_POST['credencial'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === 'true';
    
    // Busca usuário pelo e-mail
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo_conta FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $credencial]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Autenticação bem sucedida
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['tipo_conta'] = $usuario['tipo_conta'];

        // Define a rota de redirecionamento por Papel (Role)
        $redirect = ($usuario['tipo_conta'] === 'professor') ? "dashboard_prof.php" : "dashboard_adm.php";
        
        if ($is_ajax) {
            echo json_encode(['sucesso' => true, 'redirect' => $redirect]);
        } else {
            header("Location: " . $redirect);
        }
        exit();
    } else {
        // Falha na autenticação
        $mensagem_erro = "Usuário ou senha incorreta. Tente Novamente";
        
        if ($is_ajax) {
            echo json_encode(['sucesso' => false, 'erro' => $mensagem_erro]);
        } else {
            echo "<script>alert('$mensagem_erro'); window.history.back();</script>";
        }
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
