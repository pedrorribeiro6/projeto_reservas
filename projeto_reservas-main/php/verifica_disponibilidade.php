<?php
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('professor'); // Proteção

header('Content-Type: application/json');

$ESTOQUE_PCS = 35;
$ESTOQUE_TABS = 24;
$ESTOQUE_CELS = 12;

$data = $_GET['data'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fim = $_GET['fim'] ?? '';

if (empty($data) || empty($inicio) || empty($fim)) {
    echo json_encode(['sucesso' => false, 'computadores' => $ESTOQUE_PCS, 'tablets' => $ESTOQUE_TABS, 'celulares' => $ESTOQUE_CELS]);
    exit();
}

try {
    // Pega todas as reservas que cruzam o mesmo horário (Overlapping)
    $stmt = $pdo->prepare("
        SELECT horario_inicio, horario_fim, qtd_computadores, qtd_tablets, qtd_celulares 
        FROM agendamentos 
        WHERE data_reserva = ? AND horario_inicio < ? AND horario_fim > ?
    ");
    $stmt->execute([$data, $fim, $inicio]);
    $reservas = $stmt->fetchAll();

    // Algoritmo de Linha de Varredura (Sweep-Line) para calcular o pico de concorrência
    $eventos = [];
    foreach ($reservas as $res) {
        $eventos[] = ['time' => $res['horario_inicio'], 'tipo' => 1, 'pcs' => $res['qtd_computadores'], 'tabs' => $res['qtd_tablets'], 'cels' => $res['qtd_celulares']];
        $eventos[] = ['time' => $res['horario_fim'], 'tipo' => -1, 'pcs' => $res['qtd_computadores'], 'tabs' => $res['qtd_tablets'], 'cels' => $res['qtd_celulares']];
    }

    usort($eventos, function($a, $b) {
        if ($a['time'] == $b['time']) return $a['tipo'] - $b['tipo']; // Tipo -1 vem antes de 1 (libera antes de alocar na mesma exata hora)
        return strcmp($a['time'], $b['time']);
    });

    $current_pcs = 0; $current_tabs = 0; $current_cels = 0;
    $max_pcs = 0; $max_tabs = 0; $max_cels = 0;

    foreach ($eventos as $e) {
        if ($e['tipo'] == 1) {
            $current_pcs += $e['pcs'];
            $current_tabs += $e['tabs'];
            $current_cels += $e['cels'];
        } else {
            $current_pcs -= $e['pcs'];
            $current_tabs -= $e['tabs'];
            $current_cels -= $e['cels'];
        }
        if ($current_pcs > $max_pcs) $max_pcs = $current_pcs;
        if ($current_tabs > $max_tabs) $max_tabs = $current_tabs;
        if ($current_cels > $max_cels) $max_cels = $current_cels;
    }

    // Calcula os equipamentos ainda disponíveis (Max Global - Pico de Uso simultâneo)
    $disp_pcs = max(0, $ESTOQUE_PCS - $max_pcs);
    $disp_tabs = max(0, $ESTOQUE_TABS - $max_tabs);
    $disp_cels = max(0, $ESTOQUE_CELS - $max_cels);

    echo json_encode(['sucesso' => true, 'computadores' => $disp_pcs, 'tablets' => $disp_tabs, 'celulares' => $disp_cels]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno de banco de dados.']);
}
?>
