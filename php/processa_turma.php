<?php
ob_start();
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('admin'); // Apenas administradores podem gerenciar turmas e matérias

header('Content-Type: application/json');

function responder(array $dados): void {
    ob_clean();
    echo json_encode($dados);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? '';

    if (empty($acao)) {
        responder(['sucesso' => false, 'erro' => 'Ação não especificada.']);
    }

    try {
        switch ($acao) {
            case 'criar_turma':
                $nome = trim($_POST['nome'] ?? '');
                $segmento = $_POST['segmento'] ?? '';
                $periodo = $_POST['periodo'] ?? '';
                $materias = $_POST['materias'] ?? []; // Array de IDs de matérias

                if (empty($nome) || empty($segmento) || empty($periodo)) {
                    responder(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.']);
                }

                if (!in_array($segmento, ['fundamental', 'medio']) || !in_array($periodo, ['manha', 'tarde'])) {
                    responder(['sucesso' => false, 'erro' => 'Segmento ou Período inválido.']);
                }

                // Verifica se a turma já existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM turmas WHERE nome = ?");
                $stmt->execute([$nome]);
                if ($stmt->fetchColumn() > 0) {
                    responder(['sucesso' => false, 'erro' => 'Já existe uma turma com este nome.']);
                }

                $pdo->beginTransaction();

                // Insere a turma
                $stmt = $pdo->prepare("INSERT INTO turmas (nome, segmento, periodo) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $segmento, $periodo]);
                $id_turma = $pdo->lastInsertId();

                // Associa as matérias
                if (!empty($materias)) {
                    $stmt_link = $pdo->prepare("INSERT INTO turma_materias (id_turma, id_materia) VALUES (?, ?)");
                    foreach ($materias as $id_materia) {
                        $stmt_link->execute([$id_turma, (int)$id_materia]);
                    }
                }

                $pdo->commit();
                responder(['sucesso' => true]);
                break;

            case 'editar_turma':
                $id = (int)($_POST['id'] ?? 0);
                $nome = trim($_POST['nome'] ?? '');
                $periodo = $_POST['periodo'] ?? '';
                $materias = $_POST['materias'] ?? []; // Array de IDs de matérias

                if ($id <= 0 || empty($nome) || empty($periodo)) {
                    responder(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.']);
                }

                if (!in_array($periodo, ['manha', 'tarde'])) {
                    responder(['sucesso' => false, 'erro' => 'Período inválido.']);
                }

                // Verifica se o nome já existe em outra turma
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM turmas WHERE nome = ? AND id != ?");
                $stmt->execute([$nome, $id]);
                if ($stmt->fetchColumn() > 0) {
                    responder(['sucesso' => false, 'erro' => 'Já existe outra turma com este nome.']);
                }

                $pdo->beginTransaction();

                // Atualiza a turma (o segmento não costuma mudar após criação, mas atualizamos período e nome)
                $stmt = $pdo->prepare("UPDATE turmas SET nome = ?, periodo = ? WHERE id = ?");
                $stmt->execute([$nome, $periodo, $id]);

                // Limpa as associações de matérias antigas
                $stmt_clear = $pdo->prepare("DELETE FROM turma_materias WHERE id_turma = ?");
                $stmt_clear->execute([$id]);

                // Associa as novas matérias
                if (!empty($materias)) {
                    $stmt_link = $pdo->prepare("INSERT INTO turma_materias (id_turma, id_materia) VALUES (?, ?)");
                    foreach ($materias as $id_materia) {
                        $stmt_link->execute([$id, (int)$id_materia]);
                    }
                }

                $pdo->commit();
                responder(['sucesso' => true]);
                break;

            case 'excluir_turma':
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    responder(['sucesso' => false, 'erro' => 'ID da turma inválido.']);
                }

                $stmt = $pdo->prepare("DELETE FROM turmas WHERE id = ?");
                $stmt->execute([$id]);

                responder(['sucesso' => true]);
                break;

            case 'criar_materia':
                $nome = trim($_POST['nome'] ?? '');

                if (empty($nome)) {
                    responder(['sucesso' => false, 'erro' => 'O nome da matéria não pode estar vazio.']);
                }

                // Verifica se já existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM materias WHERE nome = ?");
                $stmt->execute([$nome]);
                if ($stmt->fetchColumn() > 0) {
                    responder(['sucesso' => false, 'erro' => 'Já existe uma disciplina com este nome.']);
                }

                $stmt = $pdo->prepare("INSERT INTO materias (nome) VALUES (?)");
                $stmt->execute([$nome]);

                responder(['sucesso' => true]);
                break;

            case 'editar_materia':
                $id = (int)($_POST['id'] ?? 0);
                $nome = trim($_POST['nome'] ?? '');

                if ($id <= 0 || empty($nome)) {
                    responder(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.']);
                }

                // Verifica se o nome já existe em outra matéria
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM materias WHERE nome = ? AND id != ?");
                $stmt->execute([$nome, $id]);
                if ($stmt->fetchColumn() > 0) {
                    responder(['sucesso' => false, 'erro' => 'Já existe outra disciplina com este nome.']);
                }

                $stmt = $pdo->prepare("UPDATE materias SET nome = ? WHERE id = ?");
                $stmt->execute([$nome, $id]);

                responder(['sucesso' => true]);
                break;

            case 'excluir_materia':
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    responder(['sucesso' => false, 'erro' => 'ID da matéria inválido.']);
                }

                $stmt = $pdo->prepare("DELETE FROM materias WHERE id = ?");
                $stmt->execute([$id]);

                responder(['sucesso' => true]);
                break;

            default:
                responder(['sucesso' => false, 'erro' => 'Ação desconhecida.']);
                break;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        responder(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
    }
} else {
    responder(['sucesso' => false, 'erro' => 'Método de requisição inválido.']);
}
?>
