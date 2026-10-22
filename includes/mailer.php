<?php
/**
 * Módulo de Envio de E-mails e Notificações
 * Timer de Provas
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Envia o e-mail de confirmação de cadastro para o coordenador
 *
 * @param string $nome Nome completo do coordenador
 * @param string $email E-mail de destino
 * @param string $cpf CPF formatado
 * @return bool
 */
function sendWelcomeEmail(string $nome, string $email, string $cpf): bool {
    $assunto = "Confirmação de Cadastro - " . APP_NAME;
    $loginUrl = defined('BASE_URL') ? BASE_URL . '/login.php' : 'login.php';
    $dataHora = date('d/m/Y H:i');

    $mensagemHtml = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmação de Cadastro</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
            .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; }
            .header { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #ffffff; padding: 30px 24px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
            .header p { margin: 6px 0 0 0; opacity: 0.9; font-size: 14px; }
            .content { padding: 24px; }
            .badge { display: inline-block; background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 12px; margin-bottom: 16px; }
            .info-box { background: #f9fafb; border-left: 4px solid #3b82f6; padding: 14px 16px; border-radius: 4px; margin: 20px 0; }
            .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 15px; margin-top: 16px; }
            .footer { background: #f9fafb; text-align: center; padding: 16px; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='header'>
                <h1>⏱️ " . htmlspecialchars(APP_NAME) . "</h1>
                <p>Sistema Oficial de Gerenciamento e Alertas de Provas</p>
            </div>
            <div class='content'>
                <span class='badge'>Cadastro Efetuado com Sucesso</span>
                <p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
                <p>Seu registro como <strong>Coordenador de Provas</strong> foi concluído e seu acesso está liberado.</p>
                
                <div class='info-box'>
                    <p style='margin: 0 0 6px 0;'><strong>Dados de Acesso:</strong></p>
                    <p style='margin: 0;'><strong>E-mail:</strong> " . htmlspecialchars($email) . "</p>
                    <p style='margin: 0;'><strong>CPF:</strong> " . htmlspecialchars($cpf) . "</p>
                    <p style='margin: 0;'><strong>Data do Registro:</strong> " . $dataHora . "</p>
                </div>

                <p>Você já pode acessar o sistema para configurar o timer das suas avaliações, definir alertas sonoros e visuais, e acompanhar as entregas de provas em tempo real.</p>

                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($loginUrl) . "' class='btn'>Acessar Sistema Agora</a>
                </p>
            </div>
            <div class='footer'>
                <p style='margin: 0;'>Este é um e-mail automático gerado pelo sistema " . htmlspecialchars(APP_NAME) . ".</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Timer de Provas <noreply@timerprovas.local>',
        'Reply-To: noreply@timerprovas.local',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Tentativa de envio com a função mail nativa
    $enviado = false;
    try {
        // Suprime warning caso não haja MTA/sendmail local configurado
        $enviado = @mail($email, $assunto, $mensagemHtml, implode("\r\n", $headers));
    } catch (Exception $e) {
        $enviado = false;
    }

    // Registra log do envio no disco para auditoria e teste em ambiente local (XAMPP sem SMTP configurado)
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/emails_enviados.log';
    $logEntry = sprintf(
        "[%s] Para: %s <%s> | CPF: %s | Status: %s\n",
        date('Y-m-d H:i:s'),
        $nome,
        $email,
        $cpf,
        $enviado ? 'ENVIADO_VIA_MAIL' : 'SALVO_LOCAL_SEM_SMTP'
    );
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    return true; // Retorna true para não impedir o cadastro caso o SMTP local não esteja ativo
}
