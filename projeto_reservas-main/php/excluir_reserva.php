<?php
ob_start(); // Captura qualquer saída indesejada (notices, warnings) antes do JSON
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('professor'); // Somente professor ou admin podem acessar

header('Content-Type: application/json');

// Função auxiliar: limpa o buffer e emite JSON puro
function responder(array $dados): void {
    ob_clean();
    echo json_encode($dados);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)($_POST['id'] ?? 0);
    $id_professor = $_SESSION['usuario_id'];

    if ($id > 0) {
        try {
            // Se for Administrador, pode deletar qualquer reserva. Se for Professor, só deleta as próprias.
            if (is_admin()) {
                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND id_professor = ?");
                $stmt->execute([$id, $id_professor]);
            }

            if ($stmt->rowCount() > 0) {
                responder(['sucesso' => true]);
            } else {
                responder(['sucesso' => false, 'erro' => 'A reserva não foi encontrada ou você não tem permissão para excluí-la.']);
            }
        } catch (PDOException $e) {
            responder(['sucesso' => false, 'erro' => 'Erro interno no banco de dados.']);
        }
    } else {
        responder(['sucesso' => false, 'erro' => 'ID de reserva inválido.']);
    }
}
?>
