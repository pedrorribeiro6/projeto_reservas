<?php
ob_start(); // Captura qualquer saída indesejada (notices, warnings) antes do JSON
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('professor'); // Proteção

header('Content-Type: application/json');

// Função auxiliar: limpa o buffer e emite JSON puro
function responder(array $dados): void {
    ob_clean();
    echo json_encode($dados);
    exit();
}

$ESTOQUE_PCS = 35;
$ESTOQUE_TABS = 24;
$ESTOQUE_CELS = 12;

$data   = $_GET['data']   ?? '';
$inicio = $_GET['inicio'] ?? '';
$fim    = $_GET['fim']    ?? '';

if (empty($data) || empty($inicio) || empty($fim)) {
    responder(['sucesso' => false, 'erro' => 'Dados incompletos.']);
}

try {
    // 1. Busca todos os equipamentos cadastrados
    $stmt_eq = $pdo->query("SELECT id, nome, quantidade_total FROM equipamentos");
    $equipamentos = $stmt_eq->fetchAll();

    // 2. Busca todas as reservas que conflitam com o horário solicitado
    $stmt_check = $pdo->prepare("
        SELECT i.id_equipamento, SUM(i.quantidade) as total_usado
        FROM agendamento_itens i
        JOIN agendamentos a ON i.id_agendamento = a.id
        WHERE a.data_reserva = ? AND a.horario_inicio < ? AND a.horario_fim > ?
        GROUP BY i.id_equipamento
    ");
    $stmt_check->execute([$data, $fim, $inicio]);
    $usados = $stmt_check->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [id_equipamento => total_usado]

    // 3. Calcula o saldo disponível para cada equipamento
    $estoque = [];
    foreach ($equipamentos as $eq) {
        $id = $eq['id'];
        $total = (int)$eq['quantidade_total'];
        $em_uso = (int)($usados[$id] ?? 0);
        
        $disponivel = $total - $em_uso;
        $estoque[$id] = max(0, $disponivel);
    }

    responder([
        'sucesso' => true,
        'estoque' => $estoque
    ]);

} catch (PDOException $e) {
    responder(['sucesso' => false, 'erro' => 'Erro ao verificar disponibilidade: ' . $e->getMessage()]);
}
?>
