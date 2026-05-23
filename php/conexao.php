<?php
// Configuração do Fuso Horário (Crucial para a InfinityFree e horários de reservas)
date_default_timezone_set('America/Sao_Paulo');

// Configurações do Banco de Dados
// ATENÇÃO PARA PUBLICAR NA INFINITYFREE:
// 1. Altere o $host para o fornecido pela hospedagem (ex: sql123.infinityfree.com)
// 2. Altere o $dbname para o nome do banco criado lá (ex: epiz_12345678_projeto_reservas)
// 3. Altere o $user para o nome de usuário fornecido (ex: epiz_12345678)
// 4. Altere o $pass para a senha do seu painel
$host = 'localhost';
$dbname = 'projeto_reservas';
$user = 'root';
$pass = ''; // XAMPP normalmente usa senha vazia
$port = '3306'; // A InfinityFree não exige a porta, pode apagar essa linha quando publicar

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

    $pdo->exec("CREATE TABLE IF NOT EXISTS turmas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL UNIQUE,
        segmento ENUM('fundamental', 'medio') NOT NULL,
        periodo ENUM('manha', 'tarde') NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS materias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS turma_materias (
        id_turma INT NOT NULL,
        id_materia INT NOT NULL,
        PRIMARY KEY (id_turma, id_materia),
        FOREIGN KEY (id_turma) REFERENCES turmas(id) ON DELETE CASCADE,
        FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE
    )");

    // Verifica se precisa semear os dados iniciais dos Equipamentos
    $check = $pdo->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO equipamentos (nome, quantidade_total) VALUES 
            ('Computadores', 35),
            ('Tablets', 24),
            ('Celulares', 12)");
    }

    // Verifica se precisa semear as turmas e matérias iniciais
    $check_turmas = $pdo->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
    if ($check_turmas == 0) {
        // Matérias iniciais
        $default_materias = [
            'Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 
            'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física', 
            'Biologia', 'Física', 'Química', 'Filosofia', 'Sociologia', 'Empreendedorismo'
        ];
        
        $stmt_materia = $pdo->prepare("INSERT INTO materias (nome) VALUES (?) ON DUPLICATE KEY UPDATE nome=nome");
        foreach ($default_materias as $mat) {
            $stmt_materia->execute([$mat]);
        }
        
        // Turmas iniciais com suas respectivas matérias
        $default_turmas = [
            ['nome' => '6°A', 'segmento' => 'fundamental', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '7°A', 'segmento' => 'fundamental', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '8°A', 'segmento' => 'fundamental', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '9°A', 'segmento' => 'fundamental', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '9°B', 'segmento' => 'fundamental', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '6°B', 'segmento' => 'fundamental', 'periodo' => 'tarde', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '7°B', 'segmento' => 'fundamental', 'periodo' => 'tarde', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '8°B', 'segmento' => 'fundamental', 'periodo' => 'tarde', 'materias' => ['Matemática', 'Língua Portuguesa', 'Ciências da Natureza', 'Geografia', 'História', 'Língua Inglesa', 'Robótica', 'Eixo', 'Artes', 'Educação Física']],
            ['nome' => '1°EM A', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química', 'Artes', 'Educação Física', 'Filosofia', 'Sociologia', 'Empreendedorismo']],
            ['nome' => '1°EM B', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química', 'Artes', 'Educação Física', 'Filosofia', 'Sociologia', 'Empreendedorismo']],
            ['nome' => '2°EM A', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química', 'Artes', 'Educação Física', 'Filosofia', 'Sociologia']],
            ['nome' => '2°EM B', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química', 'Artes', 'Educação Física', 'Filosofia', 'Sociologia']],
            ['nome' => '3°EM A', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química']],
            ['nome' => '3°EM B', 'segmento' => 'medio', 'periodo' => 'manha', 'materias' => ['Matemática', 'Língua Portuguesa', 'Geografia', 'História', 'Língua Inglesa', 'Biologia', 'Física', 'Química']],
        ];

        $stmt_turma = $pdo->prepare("INSERT INTO turmas (nome, segmento, periodo) VALUES (?, ?, ?)");
        $stmt_link = $pdo->prepare("INSERT INTO turma_materias (id_turma, id_materia) VALUES (?, ?)");
        
        foreach ($default_turmas as $dt) {
            $stmt_turma->execute([$dt['nome'], $dt['segmento'], $dt['periodo']]);
            $id_turma = $pdo->lastInsertId();
            
            foreach ($dt['materias'] as $mat_nome) {
                $stmt_get_mat = $pdo->prepare("SELECT id FROM materias WHERE nome = ?");
                $stmt_get_mat->execute([$mat_nome]);
                $id_materia = $stmt_get_mat->fetchColumn();
                if ($id_materia) {
                    $stmt_link->execute([$id_turma, $id_materia]);
                }
            }
        }
    }

    // Executa migração das novas colunas na tabela agendamentos (Segmento, Turma e Disciplina)
    try {
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN segmento VARCHAR(50) DEFAULT NULL");
    } catch (PDOException $e) { /* Coluna já existe */ }
    try {
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN ano_turma VARCHAR(50) DEFAULT NULL");
    } catch (PDOException $e) { /* Coluna já existe */ }
    try {
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN disciplina VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) { /* Coluna já existe */ }

    // Executa a limpeza automática silenciosa de reservas expiradas
    require_once __DIR__ . '/auto_cleanup.php';

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>