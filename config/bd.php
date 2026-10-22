<?php
/**
 * Conexão com o Banco de Dados via PDO
 * Timer de Provas
 *
 * Suporta MySQL nativamente conforme especificado nos requisitos,
 * e possui fallback automático para SQLite local caso o servidor MySQL
 * ainda não tenha sido iniciado no localhost pelo usuário.
 */

require_once __DIR__ . '/config.php';

// Parâmetros de Conexão com o Banco de Dados MySQL
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'timer_provas');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Driver atual em uso ('mysql' ou 'sqlite')
$GLOBALS['DB_DRIVER'] = 'mysql';

/**
 * Retorna uma instância de conexão PDO singleton
 *
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // 1. Tentar conexão prioritária com MySQL
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $GLOBALS['DB_DRIVER'] = 'mysql';
        return $pdo;
    } catch (PDOException $e) {
        // Se falhar a conexão com MySQL, tenta fallback inteligente para SQLite local
        $sqlitePath = __DIR__ . '/../database.sqlite';
        try {
            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]);
            $GLOBALS['DB_DRIVER'] = 'sqlite';

            // Inicializar tabelas no SQLite se arquivo for novo
            initSqliteSchema($pdo);
            return $pdo;
        } catch (Exception $sqliteErr) {
            error_log('Erro ao inicializar SQLite: ' . $sqliteErr->getMessage());
            throw $e;
        }
    }
}

/**
 * Criação automática das tabelas no SQLite caso o MySQL esteja offline
 */
function initSqliteSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            cpf TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'ativo',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS logs_auditoria (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NULL,
            usuario_nome TEXT NULL,
            acao TEXT NOT NULL,
            detalhes TEXT NULL,
            ip TEXT NULL,
            user_agent TEXT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS provas_configuracoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL UNIQUE,
            titulo TEXT NOT NULL DEFAULT 'Avaliação Oficial',
            tempo_total_minutos INTEGER NOT NULL DEFAULT 120,
            tempo_alerta_minutos INTEGER NOT NULL DEFAULT 15,
            intervalo_alerta_minutos INTEGER NOT NULL DEFAULT 5,
            emitir_som INTEGER NOT NULL DEFAULT 1,
            emitir_aviso_visual INTEGER NOT NULL DEFAULT 1,
            tipo_som TEXT NOT NULL DEFAULT 'beep_duplo',
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS provas_historico (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            titulo TEXT NOT NULL,
            tempo_total_segundos INTEGER NOT NULL,
            tempo_decorrido_segundos INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'concluida',
            resultado_posicoes TEXT NULL,
            iniciado_em DATETIME NOT NULL,
            finalizado_em DATETIME NULL,
            observacoes TEXT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
        );
    ");

    // Inserir coordenador de teste caso não exista
    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM usuarios");
    if ($stmtCheck->fetchColumn() == 0) {
        $stmtInsert = $pdo->prepare("
            INSERT INTO usuarios (nome, email, cpf, senha, status, criado_em)
            VALUES (
                'Coordenador Demonstração',
                'coordenador@etec.sp.gov.br',
                '123.456.789-00',
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'ativo',
                datetime('now', 'localtime')
            )
        ");
        $stmtInsert->execute();

        $stmtConfig = $pdo->prepare("
            INSERT INTO provas_configuracoes (usuario_id, titulo, tempo_total_minutos, tempo_alerta_minutos, intervalo_alerta_minutos, emitir_som, emitir_aviso_visual, tipo_som)
            VALUES (1, 'Avaliação Padrão', 120, 15, 5, 1, 1, 'beep_duplo')
        ");
        $stmtConfig->execute();
    }
}
