<?php
// config.php - Mictlán OS Core Configuration

// 1. Configuración de la Base de Datos (InfinityFree)
$host = "sql102.infinityfree.com"; 
$user = "if0_41576160";
$pass = "Alcudia246"; // Tu contraseña de Hosting
$db   = "if0_41576160_nexus"; 

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8"); // Evita problemas con nombres o acentos

if ($conn->connect_error) {
    die("SYSTEM_FAILURE_DB: " . $conn->connect_error);
}

// 2. Variables Globales para los Scripts (Panel y Bot)
// Estas son las que usan admin_panel.php y process_card.php
$bot_token = "TU_BOT_TOKEN_AQUI"; // El de BotFather
$chat_id   = "TU_CHAT_ID_AQUI";   // Tu ID personal

// 3. Constantes de Pasarela y Telegram (Legado)
define('CONFIRMO_API_KEY', 'TU_API_KEY_DE_CONFIRMO'); 
define('SANDBOX_MODE', true);
define('TELEGRAM_BOT_TOKEN', $bot_token);
define('TELEGRAM_CHAT_ID', $chat_id);

// Datos para recibir depósitos SPEI
$mi_banco = "SANTANDER"; 
$mi_clabe = "0123 4567 8901 2345 67"; // Tu CLABE real
$mi_beneficiario = "NEXUS SYSTEMS S.A.";

// 4. Configuración de Errores (Activo para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 5. Configuración Regional (México)
date_default_timezone_set('America/Mexico_City');

// Función auxiliar para enviar notificaciones a tu Telegram
function notifyTelegram($mensaje) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage?chat_id=" . TELEGRAM_CHAT_ID . "&text=" . urlencode($mensaje) . "&parse_mode=Markdown";
    @file_get_contents($url);
}
?>
