<?php
// webhook_confirmo.php
require '../includes/config.php'; // Ajustado a tu estructura de carpetas

// Confirmo envía los datos en formato JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    exit("No data received");
}

/**
 * LÓGICA DE CONFIRMACIÓN DE CONFIRMO
 * Status esperado: 'paid' o 'confirmed'
 */
if (isset($data['status']) && ($data['status'] === 'paid' || $data['status'] === 'confirmed')) {
    
    $invoice_id = $data['id'];            // ID de factura en Confirmo
    $monto_real  = floatval($data['amount']); // Monto real que entró
    $user_id    = intval($data['reference']); // El ID de usuario que enviamos en la referencia

    // 1. Consultamos el estado actual del usuario y su árbol de referidos
    $stmt = $conn->prepare("SELECT saldo_congelado, referido_por_id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $monto_final = $monto_real;
        $es_bienvenida = false;

        // A. LÓGICA DE "ILUSIÓN" (Bono x2 si es su primer depósito)
        if ($res['saldo_congelado'] <= 0) {
            $monto_final = $monto_real * 2;
            $es_bienvenida = true;
        }

        // B. ACTUALIZAR SALDO EN EL DASHBOARD (Para que el usuario lo vea)
        $update = $conn->prepare("UPDATE usuarios SET saldo_congelado = saldo_congelado + ? WHERE id = ?");
        $update->bind_param("di", $monto_final, $user_id);
        $update->execute();

        // C. REGISTRAR EL MOVIMIENTO EN LOGS
        $detalle = "DEP_CRYPTO_CONFIRMO" . ($es_bienvenida ? "_BONO_X2" : "");
        $log = $conn->prepare("INSERT INTO log_transacciones (usuario_id, monto, tipo, detalle) VALUES (?, ?, 'deposito', ?)");
        $log->bind_param("ids", $user_id, $monto_final, $detalle);
        $log->execute();

        // D. COMISIÓN AL REFERIDOR (10% del monto real)
        if (!empty($res['referido_por_id'])) {
            $id_padre = $res['referido_por_id'];
            $comision = $monto_real * 0.10;
            $conn->query("UPDATE usuarios SET saldo_referidos = saldo_referidos + $comision WHERE id = $id_padre");
        }

        // 2. NOTIFICACIÓN ESTRATÉGICA A TELEGRAM
        $msg = "💎 **PAGO CRYPTO CONFIRMADO** 💎\n";
        $msg .= "----------------------------\n";
        $msg .= "👤 **Usuario ID:** #$user_id\n";
        $msg .= "🧾 **Invoice:** $invoice_id\n";
        $msg .= "💵 **Monto Real:** $$monto_real\n";
        if ($es_bienvenida) $msg .= "🚀 **Bonus:** X2 ACTIVADO\n";
        $msg .= "📈 **Saldo Dashboard:** $$monto_final\n";
        $msg .= "----------------------------\n";
        $msg .= "✅ _Inyección completada en base de datos._";

        // Función de notificación (Asegúrate de que esté definida en config.php o usa la URL directa)
        $url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";
        @file_get_contents($url);
        
        // 3. RESPUESTA EXITOSA A CONFIRMO
        http_response_code(200); 
        echo json_encode(["status" => "ok"]);
    }
} else {
    // Si el estado no es 'paid', ignoramos pero respondemos 200 para que no reintenten
    http_response_code(200);
}
?>
