<?php
// Configurações do Banco de Dados
$host = 'localhost';
$port = '3307';
$dbname = 'projeto_reservas';
$user = 'root';
$pass = ''; // XAMPP normalmente usa senha vazia

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Inicialização automática das tabelas de Equipamentos (se não existirem)
    $pdo->exec("CREATE TABLE IF NOT EXISTS equipamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        quantidade_total INT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS agendamento_itens (
        id_agendamento INT NOT NULL,
        id_equipamento INT NOT NULL,
        quantidade INT NOT NULL,
        PRIMARY KEY (id_agendamento, id_equipamento)
    )");

    // Verifica se precisa semear os dados iniciais
    $check = $pdo->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO equipamentos (nome, quantidade_total) VALUES 
            ('Computadores', 35),
            ('Tablets', 24),
            ('Celulares', 12)");
    }

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>