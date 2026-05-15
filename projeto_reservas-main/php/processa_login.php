<?php
session_start();
require 'conexao.php';

// Mock de usuário professor para facilitar os testes se não existir
try {
    $checkProf = $pdo->query("SELECT id FROM usuarios WHERE email = 'prof@teste.com'")->fetch();
    if (!$checkProf) {
        $hash = password_hash('prof123', PASSWORD_BCRYPT);
        $pdo->query("INSERT INTO usuarios (nome, email, senha, tipo_conta) VALUES ('Professor Teste', 'prof@teste.com', '$hash', 'professor')");
    }
} catch (Exception $e) {}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $credencial = $_POST['credencial'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Busca usuário pelo e-mail
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo_conta FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $credencial]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Autenticação bem sucedida
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['tipo_conta'] = $usuario['tipo_conta'];

        // Redirecionamento por Papel (Role)
        if ($usuario['tipo_conta'] === 'professor') {
            header("Location: dashboard_prof.php");
        } else {
            // Se for admin
            header("Location: dashboard_adm.php");
        }
        exit();
    } else {
        // Falha
        echo "<script>alert('Credenciais inválidas! (Dica: use prof@teste.com / prof123)'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
