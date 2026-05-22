<?php
ob_start(); // Captura qualquer saída indesejada antes do JSON
session_start();
require_once 'auth.php';
require_once 'conexao.php';

// Proteção da página para usuários logados
proteger_pagina('professor');

header('Content-Type: application/json');

// Como o arquivo conexao.php já inclui e executa o auto_cleanup.php internamente,
// ao chegar nesta linha as reservas expiradas já foram limpas com sucesso.

$timezone = new DateTimeZone('America/Sao_Paulo');
$agora = new DateTime('now', $timezone);

ob_clean();
echo json_encode([
    'sucesso' => true,
    'timestamp' => $agora->format('d/m/Y H:i:s'),
    'mensagem' => 'Exclusão automática de agendamentos expirados executada com sucesso.'
]);
exit;
?>
