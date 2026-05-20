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
            $pdo->beginTransaction();

            if (is_admin()) {
                // Remove primeiro os itens da reserva para manter integridade
                $stmt_items = $pdo->prepare("DELETE FROM agendamento_itens WHERE id_agendamento = ?");
                $stmt_items->execute([$id]);

                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                // No caso do professor, precisamos verificar a posse antes de deletar itens
                $stmt_check = $pdo->prepare("SELECT id FROM agendamentos WHERE id = ? AND id_professor = ?");
                $stmt_check->execute([$id, $id_professor]);
                
                if ($stmt_check->rowCount() > 0) {
                    $stmt_items = $pdo->prepare("DELETE FROM agendamento_itens WHERE id_agendamento = ?");
                    $stmt_items->execute([$id]);

                    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND id_professor = ?");
                    $stmt->execute([$id, $id_professor]);
                } else {
                    $pdo->rollBack();
                    responder(['sucesso' => false, 'erro' => 'A reserva não foi encontrada ou você não tem permissão para excluí-la.']);
                }
            }

            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                responder(['sucesso' => true]);
            } else {
                $pdo->rollBack();
                responder(['sucesso' => false, 'erro' => 'Falha ao processar exclusão.']);
            }
        } catch (PDOException $e) {
            responder(['sucesso' => false, 'erro' => 'Erro interno no banco de dados.']);
        }
    } else {
        responder(['sucesso' => false, 'erro' => 'ID de reserva inválido.']);
    }
}
?>
