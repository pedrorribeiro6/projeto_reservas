<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('professor');

header('Content-Type: application/json');

$segmento = $_GET['segmento'] ?? '';

if (empty($segmento) || !in_array($segmento, ['fundamental', 'medio'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Segmento inválido ou não informado.']);
    exit();
}

try {
    // Busca todas as turmas do segmento solicitado
    $stmt = $pdo->prepare("SELECT id, nome, segmento, periodo FROM turmas WHERE segmento = ? ORDER BY nome ASC");
    $stmt->execute([$segmento]);
    $turmas = $stmt->fetchAll();

    $resultado = [];

    foreach ($turmas as $turma) {
        // Busca as matérias vinculadas a esta turma
        $stmt_mat = $pdo->prepare("
            SELECT m.nome 
            FROM materias m
            JOIN turma_materias tm ON tm.id_materia = m.id
            WHERE tm.id_turma = ?
            ORDER BY m.nome ASC
        ");
        $stmt_mat->execute([$turma['id']]);
        $materias = $stmt_mat->fetchAll(PDO::FETCH_COLUMN);

        $resultado[] = [
            'id' => (int)$turma['id'],
            'nome' => $turma['nome'],
            'segmento' => $turma['segmento'],
            'periodo' => $turma['periodo'],
            'materias' => $materias
        ];
    }

    echo json_encode(['sucesso' => true, 'turmas' => $resultado]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>
