<?php
ob_start(); // Captura qualquer saída indesejada (notices, warnings) antes do JSON
session_start();
require 'conexao.php';
require 'auth.php';
proteger_pagina('professor'); // Somente professor (ou admin) logado acessa aqui

header('Content-Type: application/json');

// Função auxiliar: limpa o buffer e emite JSON puro
function responder(array $dados): void {
    ob_clean();
    echo json_encode($dados);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_professor = $_SESSION['usuario_id'];
    $data_reserva = $_POST['data_reserva'] ?? '';
    $horario_inicio = $_POST['horario_inicio'] ?? '';
    $horario_fim = $_POST['horario_fim'] ?? '';
    $qtd_computadores = (int)($_POST['qtd_computadores'] ?? 0);
    $qtd_tablets = (int)($_POST['qtd_tablets'] ?? 0);
    $qtd_celulares = (int)($_POST['qtd_celulares'] ?? 0);

    // Validação de Fim de Semana (Segurança Back-end)
    $dia_semana = (int)date('w', strtotime($data_reserva)); // 0 = Domingo, 6 = Sábado
    if ($dia_semana === 0 || $dia_semana === 6) {
        responder(['sucesso' => false, 'erro' => 'Agendamentos não são permitidos aos finais de semana (Sábado e Domingo).']);
    }

    // Validação de Janela de Horário e Intervalo Escolar (Segurança Back-end)
    $h_ini = (int)str_replace(':', '', $horario_inicio);
    $h_fim = (int)str_replace(':', '', $horario_fim);

    if ($h_ini < 700 || $h_fim > 1800) {
        responder(['sucesso' => false, 'erro' => 'Fora do horário escolar permitido (07:00 às 18:00).']);
    }
    if (($h_ini > 1220 && $h_ini < 1240) || ($h_fim > 1220 && $h_fim < 1240)) {
        responder(['sucesso' => false, 'erro' => 'Não é permitido iniciar ou terminar agendamentos dentro do intervalo (12:20 às 12:40).']);
    }
    if ($h_ini < 1240 && $h_fim > 1220) {
        responder(['sucesso' => false, 'erro' => 'A reserva não pode atravessar o horário do intervalo (12:20 às 12:40).']);
    }

    // Validação de Overbooking com Algoritmo Sweep-Line no lado Servidor (Impede concorrência e hacks)
    $ESTOQUE_PCS = 35;
    $ESTOQUE_TABS = 24;
    $ESTOQUE_CELS = 12;

    $stmt_check = $pdo->prepare("SELECT horario_inicio, horario_fim, qtd_computadores, qtd_tablets, qtd_celulares FROM agendamentos WHERE data_reserva = ? AND horario_inicio < ? AND horario_fim > ?");
    $stmt_check->execute([$data_reserva, $horario_fim, $horario_inicio]);
    $reservas = $stmt_check->fetchAll();

    $eventos = [];
    foreach ($reservas as $res) {
        $eventos[] = ['time' => $res['horario_inicio'], 'tipo' => 1, 'pcs' => $res['qtd_computadores'], 'tabs' => $res['qtd_tablets'], 'cels' => $res['qtd_celulares']];
        $eventos[] = ['time' => $res['horario_fim'], 'tipo' => -1, 'pcs' => $res['qtd_computadores'], 'tabs' => $res['qtd_tablets'], 'cels' => $res['qtd_celulares']];
    }

    // Insere o pedido atual na matriz de eventos para simular se causaria explosão de estoque
    $eventos[] = ['time' => $horario_inicio, 'tipo' => 1, 'pcs' => $qtd_computadores, 'tabs' => $qtd_tablets, 'cels' => $qtd_celulares];
    $eventos[] = ['time' => $horario_fim, 'tipo' => -1, 'pcs' => $qtd_computadores, 'tabs' => $qtd_tablets, 'cels' => $qtd_celulares];

    usort($eventos, function($a, $b) {
        if ($a['time'] == $b['time']) return $a['tipo'] - $b['tipo'];
        return strcmp($a['time'], $b['time']);
    });

    $current_pcs = 0; $current_tabs = 0; $current_cels = 0;
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

        if ($current_pcs > $ESTOQUE_PCS || $current_tabs > $ESTOQUE_TABS || $current_cels > $ESTOQUE_CELS) {
            responder(['sucesso' => false, 'erro' => 'Conflito Crítico! O limite de equipamentos estourou nesse horário exato devido a outra reserva simultânea.']);
        }
    }
    
    if ($qtd_computadores == 0 && $qtd_tablets == 0 && $qtd_celulares == 0) {
        responder(['sucesso' => false, 'erro' => 'Nenhum equipamento foi selecionado.']);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO agendamentos (id_professor, qtd_computadores, qtd_tablets, qtd_celulares, data_reserva, horario_inicio, horario_fim) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_professor, $qtd_computadores, $qtd_tablets, $qtd_celulares, $data_reserva, $horario_inicio, $horario_fim]);
        responder(['sucesso' => true]);
    } catch (PDOException $e) {
        responder(['sucesso' => false, 'erro' => 'Erro interno do banco: ' . $e->getMessage()]);
    }
} else {
    responder(['sucesso' => false, 'erro' => 'Método inválido.']);
}
?>
