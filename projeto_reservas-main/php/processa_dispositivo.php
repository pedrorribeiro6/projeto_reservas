<?php
ob_start();
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('admin');

header('Content-Type: application/json');

function responder(array $dados): void {
    ob_clean();
    echo json_encode($dados);
    exit();
}

$acao = $_POST['acao'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. ADICIONAR OU EDITAR
    if ($acao === 'salvar') {
        $id = (int)($_POST['id'] ?? 0);
        $nome = $_POST['nome'] ?? '';
        $qtd = (int)($_POST['quantidade_total'] ?? 0);

        if (empty($nome) || $qtd <= 0) {
            responder(['sucesso' => false, 'erro' => 'Preencha todos os campos corretamente.']);
        }

        try {
            if ($id > 0) {
                // EDITAR
                $stmt = $pdo->prepare("UPDATE equipamentos SET nome = ?, quantidade_total = ? WHERE id = ?");
                $stmt->execute([$nome, $qtd, $id]);
            } else {
                // ADICIONAR
                $stmt = $pdo->prepare("INSERT INTO equipamentos (nome, quantidade_total) VALUES (?, ?)");
                $stmt->execute([$nome, $qtd]);
            }
            responder(['sucesso' => true]);
        } catch (PDOException $e) {
            responder(['sucesso' => false, 'erro' => 'Erro no banco: ' . $e->getMessage()]);
        }
    }

    // 2. EXCLUIR
    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);

        try {
            // Verifica se há agendamentos REAIS usando este equipamento (fazendo JOIN com a tabela pai)
            $stmt_check = $pdo->prepare("
                SELECT COUNT(*) 
                FROM agendamento_itens i
                JOIN agendamentos a ON i.id_agendamento = a.id
                WHERE i.id_equipamento = ?
            ");
            $stmt_check->execute([$id]);
            
            if ($stmt_check->fetchColumn() > 0) {
                responder(['sucesso' => false, 'erro' => 'Não é possível excluir: este dispositivo ainda possui reservas vinculadas no sistema.']);
            }

            $stmt = $pdo->prepare("DELETE FROM equipamentos WHERE id = ?");
            $stmt->execute([$id]);
            responder(['sucesso' => true]);
        } catch (PDOException $e) {
            responder(['sucesso' => false, 'erro' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }
}

responder(['sucesso' => false, 'erro' => 'Ação inválida.']);
