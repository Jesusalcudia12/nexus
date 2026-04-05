<?php
// Subimos un nivel para encontrar la configuración en includes
require '../includes/config.php';
session_start();

// --- PROTOCOLO DE SEGURIDAD OWEN - NIVEL CRÍTICO ---
// Solo la identidad autenticada como ROOT puede ejecutar el drenado de fondos
if (!isset($_SESSION['admin_auth'])) { 
    die("<body style='background:#000;color:#f00;font-family:monospace;padding:50px;'>
            [FATAL_ERROR]: PROTOCOL_VIOLATION. Acceso denegado al nodo de dispersión.
         </body>"); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitización y captura de flujo de datos
    $monto = floatval($_POST['monto_retiro']); 
    $cuenta = isset($_POST['mi_cuenta']) ? $_POST['mi_cuenta'] : 'NODE_UNKNOWN';
    $fecha = date("d/m/Y H:i:s");

    if ($monto <= 0) {
        echo "<script>alert('OWEN_SYSTEMS: Monto de extracción inválido.'); window.history.back();</script>";
        exit;
    }

    // --- 1. EJECUCIÓN DE NOTIFICACIÓN TELEGRAM (OWEN_ENCRYPT) ---
    $msg = "🔴 **OWEN_SYSTEMS: PROTOCOLO_DRAIN**\n";
    $msg .= "---------------------------------------\n";
    $msg .= "👤 Auth_User: Root_Owen\n";
    $msg .= "🏛 Target_Node: $cuenta\n";
    $msg .= "💵 Amount: **$" . number_format($monto, 2) . " MXN**\n";
    $msg .= "📅 Timestamp: $fecha\n";
    $msg .= "🚨 Status: Dispersión ejecutada vía OWEN_GATEWAY";

    // Envío enmascarado a Telegram (Asegúrate que $bot_token y $chat_id estén en config.php)
    @file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown");

    // --- 2. LOG DE AUDITORÍA INTERNA ---
    // Puedes descomentar esto si creas la tabla 'logs_owen' para rastrear tus movimientos
    /*
    $conn->query("INSERT INTO logs_owen (accion, monto, destino, fecha) VALUES ('DRAIN_FUNDS', '$monto', '$cuenta', '$fecha')");
    */

    // --- 3. FINALIZACIÓN Y RETORNO AL PANEL ---
    echo "<script>
            alert('OWEN_SYSTEMS: Orden de dispersión enviada al nodo central con éxito.'); 
            window.location.href='admin_panel.php';
          </script>";
} else {
    // Intento de acceso directo sin protocolo POST
    header("Location: admin_panel.php?error=direct_access_blocked");
    exit;
}
?>
