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
    $quantidades = $_POST['quantidade'] ?? []; // Array [id_equipamento => quantidade]

    // Validação de Fim de Semana
    $dia_semana = (int)date('w', strtotime($data_reserva));
    if ($dia_semana === 0 || $dia_semana === 6) {
        responder(['sucesso' => false, 'erro' => 'Agendamentos não são permitidos aos finais de semana.']);
    }

    // Validação de Horário
    $h_ini = (int)str_replace(':', '', $horario_inicio);
    $h_fim = (int)str_replace(':', '', $horario_fim);

    if ($h_ini < 700 || $h_fim > 1800) {
        responder(['sucesso' => false, 'erro' => 'Fora do horário escolar permitido (07:00 às 18:00).']);
    }
    if ($h_ini < 1240 && $h_fim > 1220) {
        responder(['sucesso' => false, 'erro' => 'A reserva não pode atravessar o horário do intervalo (12:20 às 12:40).']);
    }

    try {
        $pdo->beginTransaction();

        // 1. Busca todos os equipamentos para validar estoque total e existência
        $stmt_eq = $pdo->query("SELECT * FROM equipamentos");
        $equipamentos = $stmt_eq->fetchAll(PDO::FETCH_UNIQUE);

        // 2. Verifica disponibilidade real para cada item no horário solicitado
        $stmt_check = $pdo->prepare("
            SELECT i.id_equipamento, SUM(i.quantidade) as total_usado
            FROM agendamento_itens i
            JOIN agendamentos a ON i.id_agendamento = a.id
            WHERE a.data_reserva = ? AND a.horario_inicio < ? AND a.horario_fim > ?
            GROUP BY i.id_equipamento
        ");
        $stmt_check->execute([$data_reserva, $horario_fim, $horario_inicio]);
        $usados = $stmt_check->fetchAll(PDO::FETCH_KEY_PAIR);

        $itens_para_reservar = [];
        $total_equipamentos = 0;

        foreach ($quantidades as $id_eq => $qtd) {
            $id_eq = (int)$id_eq;
            $qtd = (int)$qtd;

            if ($qtd > 0) {
                if (!isset($equipamentos[$id_eq])) {
                    throw new Exception("Equipamento ID $id_eq não existe.");
                }

                $total_estoque = (int)$equipamentos[$id_eq]['quantidade_total'];
                $ja_reservado = (int)($usados[$id_eq] ?? 0);
                $disponivel = $total_estoque - $ja_reservado;

                if ($qtd > $disponivel) {
                    throw new Exception("Estoque insuficiente para " . $equipamentos[$id_eq]['nome'] . ". Disponível: $disponivel");
                }

                $itens_para_reservar[] = ['id' => $id_eq, 'qtd' => $qtd];
                $total_equipamentos += $qtd;
            }
        }

        if ($total_equipamentos === 0) {
            throw new Exception("Nenhum equipamento foi selecionado.");
        }

        // 3. Insere o Agendamento Principal
        $stmt = $pdo->prepare("INSERT INTO agendamentos (id_professor, data_reserva, horario_inicio, horario_fim) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_professor, $data_reserva, $horario_inicio, $horario_fim]);
        $id_agendamento = $pdo->lastInsertId();

        // 4. Insere os Itens do Agendamento
        $stmt_item = $pdo->prepare("INSERT INTO agendamento_itens (id_agendamento, id_equipamento, quantidade) VALUES (?, ?, ?)");
        foreach ($itens_para_reservar as $item) {
            $stmt_item->execute([$id_agendamento, $item['id'], $item['qtd']]);
        }

        $pdo->commit();
        responder(['sucesso' => true]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        responder(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
} else {
    responder(['sucesso' => false, 'erro' => 'Método inválido.']);
}
?>
