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
    $segmento = $_POST['segmento'] ?? '';
    $ano_turma = $_POST['ano_turma'] ?? '';
    $disciplina = $_POST['disciplina'] ?? '';
    $quantidades = $_POST['quantidade'] ?? []; // Array [id_equipamento => quantidade]

    // 1. Validação de dados obrigatórios
    if (empty($data_reserva) || empty($horario_inicio) || empty($horario_fim) || empty($segmento) || empty($ano_turma) || empty($disciplina)) {
        responder(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios (Segmento, Turma, Disciplina, Data e Período).']);
    }

    // 2. Validação de Datas e Horários Passados (Fuso Horário America/Sao_Paulo)
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $agora = new DateTime('now', $timezone);
    $data_atual = $agora->format('Y-m-d');
    $horario_atual = $agora->format('H:i');

    $h_reserva_inicio = date('H:i', strtotime($horario_inicio));

    if ($data_reserva < $data_atual || ($data_reserva === $data_atual && $h_reserva_inicio < $horario_atual)) {
        responder(['sucesso' => false, 'erro' => 'Horários já passados não estão disponíveis.']);
    }

    // 3. Validação de Fim de Semana
    $dia_semana = (int)date('w', strtotime($data_reserva));
    if ($dia_semana === 0 || $dia_semana === 6) {
        responder(['sucesso' => false, 'erro' => 'Agendamentos não são permitidos aos finais de semana.']);
    }

    // 4. Validação de limite de 2 aulas (máximo de 120 minutos considerando arredondamentos/aulas duplas)
    $t_ini = strtotime($horario_inicio);
    $t_fim = strtotime($horario_fim);
    $duracao_minutos = ($t_fim - $t_ini) / 60;

    if ($duracao_minutos > 120 || $duracao_minutos <= 0) {
        responder(['sucesso' => false, 'erro' => 'O agendamento excede o limite máximo permitido de 2 aulas.']);
    }

    // 5. Validação de Intervalos (Indisponíveis para reserva)
    function conflitaComIntervalo($pdo, $seg, $turma, $h_ini, $h_fim) {
        $ini = strtotime($h_ini);
        $fim = strtotime($h_fim);

        if ($seg === 'fundamental') {
            // Se for Fundamental II, verifica se é turma da manhã na tabela do banco
            $stmt_periodo = $pdo->prepare("SELECT periodo FROM turmas WHERE nome = ? LIMIT 1");
            $stmt_periodo->execute([$turma]);
            $periodo = $stmt_periodo->fetchColumn();
            
            $isManha = ($periodo === 'manha');
            if ($isManha) {
                $intervalos = [
                    ['10:20', '10:40']
                ];
            } else {
                $intervalos = [
                    ['15:10', '15:30']
                ];
            }
        } else {
            // Ensino Médio (todos são manhã por padrão)
            $intervalos = [
                ['10:20', '10:40']
            ];
        }

        foreach ($intervalos as $inter) {
            $int_ini = strtotime($inter[0]);
            $int_fim = strtotime($inter[1]);
            
            // Colisão se inicio da reserva é antes do fim do intervalo E fim da reserva é depois do inicio do intervalo
            if ($ini < $int_fim && $fim > $int_ini) {
                return true;
            }
        }
        return false;
    }

    if (conflitaComIntervalo($pdo, $segmento, $ano_turma, $horario_inicio, $horario_fim)) {
        responder(['sucesso' => false, 'erro' => 'Reservas não são permitidas nos horários de intervalo escolar.']);
    }

    // 6. Validação de Disciplinas e Turmas (Dinâmica a partir do banco de dados)
    $stmt_vinculo = $pdo->prepare("
        SELECT COUNT(*) 
        FROM turma_materias tm
        JOIN turmas t ON tm.id_turma = t.id
        JOIN materias m ON tm.id_materia = m.id
        WHERE t.nome = ? AND m.nome = ?
    ");
    $stmt_vinculo->execute([$ano_turma, $disciplina]);
    $hasVinculo = $stmt_vinculo->fetchColumn() > 0;
    
    if (!$hasVinculo) {
        responder(['sucesso' => false, 'erro' => "A disciplina '$disciplina' não está associada à turma '$ano_turma' no cadastro do sistema."]);
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

        // 3. Insere o Agendamento Principal com as novas colunas
        $stmt = $pdo->prepare("INSERT INTO agendamentos (id_professor, data_reserva, horario_inicio, horario_fim, segmento, ano_turma, disciplina) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_professor, $data_reserva, $horario_inicio, $horario_fim, $segmento, $ano_turma, $disciplina]);
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
