<?php
session_start();
require 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo_conta = $_POST['tipo_conta'] ?? '';

    // Validação Básica
    if (empty($nome) || empty($email) || empty($senha) || empty($tipo_conta)) {
        $_SESSION['erro_cadastro'] = "Todos os campos são obrigatórios.";
        header("Location: criacao_conta.php");
        exit();
    }

    if (!in_array($tipo_conta, ['professor', 'admin'])) {
        $_SESSION['erro_cadastro'] = "Tipo de conta inválido.";
        header("Location: criacao_conta.php");
        exit();
    }

    try {
        // Verifica se o email já existe
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->rowCount() > 0) {
            $_SESSION['erro_cadastro'] = "Este e-mail já está registrado.";
            header("Location: criacao_conta.php");
            exit();
        }

        // Criptografa a senha para máxima segurança
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Insere o usuário
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo_conta) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $hash, $tipo_conta]);

        $_SESSION['sucesso_cadastro'] = "CONTA CRIADA COM SUCESSO! Você já pode fazer o Login.";
        header("Location: criacao_conta.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['erro_cadastro'] = "Erro interno no servidor ao tentar criar a conta.";
        header("Location: criacao_conta.php");
        exit();
    }
} else {
    header("Location: criacao_conta.php");
    exit();
}
?>
