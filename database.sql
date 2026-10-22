-- Script de Criação do Banco de Dados: Timer de Provas
-- Compatível com MySQL 5.7+ e MariaDB 10.2+

CREATE DATABASE IF NOT EXISTS `timer_provas` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `timer_provas`;

-- 1. Tabela de Coordenadores / Usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `status` ENUM('ativo', 'pendente', 'inativo') NOT NULL DEFAULT 'ativo',
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Logs de Auditoria (Obrigatório por requisito)
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `usuario_nome` VARCHAR(150) NULL,
    `acao` VARCHAR(80) NOT NULL,
    `detalhes` TEXT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_acao` (`acao`),
    INDEX `idx_criado_em` (`criado_em`),
    CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Configurações do Timer salvas pelo Coordenador
CREATE TABLE IF NOT EXISTS `provas_configuracoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `titulo` VARCHAR(200) NOT NULL DEFAULT 'Prova Padrão',
    `tempo_total_minutos` INT NOT NULL DEFAULT 120,
    `tempo_alerta_minutos` INT NOT NULL DEFAULT 15,
    `intervalo_alerta_minutos` INT NOT NULL DEFAULT 5,
    `emitir_som` TINYINT(1) NOT NULL DEFAULT 1,
    `emitir_aviso_visual` TINYINT(1) NOT NULL DEFAULT 1,
    `tipo_som` VARCHAR(50) NOT NULL DEFAULT 'beep_duplo',
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_config_usuario` (`usuario_id`),
    CONSTRAINT `fk_config_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Histórico de Execuções e Resultados/Posições das Provas
CREATE TABLE IF NOT EXISTS `provas_historico` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `tempo_total_segundos` INT NOT NULL,
    `tempo_decorrido_segundos` INT NOT NULL,
    `status` ENUM('concluida', 'interrompida', 'em_andamento') NOT NULL DEFAULT 'concluida',
    `resultado_posicoes` TEXT NULL COMMENT 'JSON ou lista com ordem e entrega dos candidatos',
    `iniciado_em` DATETIME NOT NULL,
    `finalizado_em` DATETIME NULL,
    `observacoes` TEXT NULL,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_hist_usuario` (`usuario_id`),
    INDEX `idx_hist_iniciado` (`iniciado_em`),
    CONSTRAINT `fk_hist_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inserir Coordenador de demonstração inicial (opcional)
-- Senha de teste: "Admin@123" (gerada com PASSWORD_BCRYPT)
INSERT INTO `usuarios` (`nome`, `email`, `cpf`, `senha`, `status`)
VALUES (
    'Coordenador Demonstração',
    'coordenador@etec.sp.gov.br',
    '123.456.789-00',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'ativo'
) ON DUPLICATE KEY UPDATE `id`=`id`;
