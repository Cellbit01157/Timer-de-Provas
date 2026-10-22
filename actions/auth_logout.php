<?php
/**
 * Ação de Logout do Coordenador
 * Timer de Provas
 */

require_once __DIR__ . '/../includes/auth.php';

logoutUser();
setToast('info', 'Você encerrou sua sessão com segurança.', 'Desconectado');
header('Location: ../login.php');
exit;
