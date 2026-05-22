<?php
// Evita execução direta fora da conexão do banco
if (!isset($pdo)) {
    exit;
}

/**
 * Executa a limpeza automática de agendamentos expirados no banco de dados.
 * Considera o fuso horário de Brasília (America/Sao_Paulo).
 * 
 * @param PDO $pdo Conexão PDO ativa
 * @return int Quantidade de registros excluídos
 */
function executarLimpezaReservas(PDO $pdo): int {
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $agora = new DateTime('now', $timezone);
    $data_atual = $agora->format('Y-m-d');
    $horario_atual = $agora->format('H:i:s');

    try {
        // 1. Busca os IDs das reservas expiradas
        // Uma reserva está expirada se:
        // - A data da reserva é anterior à data de hoje
        // - OU a data é hoje, mas o horário de término já passou
        $stmt_busca = $pdo->prepare("
            SELECT id FROM agendamentos 
            WHERE data_reserva < :data_atual 
               OR (data_reserva = :data_atual AND horario_fim <= :horario_atual)
        ");
        $stmt_busca->execute([
            ':data_atual' => $data_atual,
            ':horario_atual' => $horario_atual
        ]);
        $ids_expirados = $stmt_busca->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids_expirados)) {
            return 0; // Nenhuma reserva expirada para excluir
        }

        // 2. Executa a exclusão em uma única transação para manter a integridade referencial
        $pdo->beginTransaction();

        $in_clause = implode(',', array_fill(0, count($ids_expirados), '?'));
        
        // Remove primeiro os itens das reservas para não ter chaves órfãs ou violação de constraints
        $stmt_itens = $pdo->prepare("DELETE FROM agendamento_itens WHERE id_agendamento IN ($in_clause)");
        $stmt_itens->execute($ids_expirados);

        // Remove os agendamentos expirados propriamente ditos
        $stmt_agendamentos = $pdo->prepare("DELETE FROM agendamentos WHERE id IN ($in_clause)");
        $stmt_agendamentos->execute($ids_expirados);

        $pdo->commit();
        return count($ids_expirados);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Grava o log do erro silenciosamente para não atrapalhar o fluxo principal da página
        error_log("Erro no auto_cleanup: " . $e->getMessage());
        return 0;
    }
}

// Executa automaticamente a cada conexão ao banco de dados (conexao.php)
executarLimpezaReservas($pdo);
?>
