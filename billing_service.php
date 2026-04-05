<?php
// procesar_pago_openpay.php
require '../includes/config.php';
session_start();

// 1. PROTOCOLO DE SEGURIDAD
if (!isset($_SESSION['user_id'])) { 
    die(json_encode(["status" => "error", "message" => "SESION_EXPIREADA"])); 
}

$user_id  = $_SESSION['user_id'];
$token_id = $_POST['token_id'] ?? '';
$device_session_id = $_POST['device_session_id'] ?? ''; // OBLIGATORIO PARA OPENPAY
$monto_base = 500.00; // Monto fijo de recarga configurado

if (!$token_id || !$device_session_id) { 
    header("Location: deposito.php?error=payload_incomplete");
    exit(); 
}

// 2. CONFIGURACIÓN DE ENDPOINT API
$url = ($sandbox) ? "https://sandbox-api.openpay.mx/v1/" : "https://api.openpay.mx/v1/";
$url .= $merchant_id . "/charges";

$payload = [
    'source_id' => $token_id,
    'method'    => 'card',
    'amount'    => $monto_base,
    'description' => 'INYECCION_NEXUS_ID_' . $user_id,
    'device_session_id' => $device_session_id,
    'customer'  => [
        'name'  => 'Usuario Nexus', 
        'email' => 'pago_verificado@nexus-os.com'
    ]
];

// 3. EJECUCIÓN DE CURL (LLAMADA A OPENPAY)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERPWD, $private_key . ":");
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

// --- 4. PROCESAMIENTO DE RESULTADOS Y LOGICA DE SISTEMA ---

if (isset($result['id']) && ($result['status'] == 'completed' || $result['status'] == 'charge_pending')) {
    
    // A. VALIDACIÓN DE BONO DE BIENVENIDA (X2 para incentivar)
    // Buscamos si es el primer pago del usuario
    $stmt_check = $conn->prepare("SELECT (SELECT COUNT(*) FROM log_transacciones WHERE usuario_id = ?) as pagos_previos, referido_por_id FROM usuarios WHERE id = ?");
    $stmt_check->bind_param("ii", $user_id, $user_id);
    $stmt_check->execute();
    $u_info = $stmt_check->get_result()->fetch_assoc();
    
    $es_primer_pago = ($u_info['pagos_previos'] == 0);
    $monto_usuario = $monto_base;
    $bono_aplicado = 0;

    if ($es_primer_pago) {
        $bono_aplicado = $monto_base; // Aplicamos 100% extra (X2)
        $monto_usuario = $monto_base + $bono_aplicado;
    }

    // B. COMISIÓN PARA EL REFERIDOR (10%)
    $comision_padre = $monto_base * 0.10;
    $padre_id = $u_info['referido_por_id'];

    // C. ACTUALIZACIÓN DE BASE DE DATOS (TRANSACCIÓN)
    $conn->begin_transaction();
    try {
        // Inyectar saldo al usuario
        $conn->query("UPDATE usuarios SET saldo_congelado = saldo_congelado + $monto_usuario WHERE id = $user_id");
        $conn->query("INSERT INTO log_transacciones (usuario_id, monto, tipo, detalle) VALUES ($user_id, $monto_base, 'deposito', 'OPENPAY_SUCCESS')");

        // Pagar comisión al padre si existe
        if ($padre_id) {
            $conn->query("UPDATE usuarios SET saldo_referidos = saldo_referidos + $comision_padre WHERE id = $padre_id");
            $conn->query("INSERT INTO log_transacciones (usuario_id, monto, tipo, detalle) VALUES ($padre_id, $comision_padre, 'comision', 'REF_ID_$user_id')");
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }

    // --- 5. AUDITORÍA TELEGRAM ---
    $banco = $result['card']['bank_name'] ?? 'DESCONOCIDO';
    $brand = $result['card']['brand'] ?? 'CARD';
    $last4 = substr($result['card']['card_number'] ?? '0000', -4);
    
    $notif = "💉 **NEXUS_INJECTION: ÉXITO**\n";
    $notif .= "---------------------------\n";
    $notif .= "👤 **ID:** #$user_id\n";
    $notif .= "💰 **Real:** $$monto_base MXN\n";
    if($bono_aplicado > 0) $notif .= "🚀 **Bonus X2:** +$$bono_aplicado (BIENVENIDA)\n";
    $notif .= "💳 **Card:** $brand ($last4)\n";
    $notif .= "🏦 **Bank:** $banco\n";
    $notif .= "✅ **Status:** COMPLETED_WITHOUT_3DS\n";
    $notif .= "---------------------------\n";
    $notif .= "📡 _Inyección de base de datos confirmada._";

    $tg_url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($notif) . "&parse_mode=Markdown";
    @file_get_contents($tg_url);

    // Redirigir con éxito
    echo "<script>alert('Sincronización Exitosa. Sus fondos se han inyectado al sistema.'); window.location.href='dashboard.php';</script>";

} else {
    // --- MANEJO DE ERRORES / RECHAZOS ---
    $error_desc = $result['description'] ?? 'Error de comunicación con el emisor.';
    $error_code = $result['error_code'] ?? '1000';
    
    // Notificar error por si es un error técnico tuyo
    if ($error_code != '3004' && $error_code != '3005') { // No notificar si es solo "fondos insuficientes" o "rechazada"
        $err_log = "⚠️ **ERROR_GATEWAY: ID #$user_id**\nCode: $error_code\nDesc: $error_desc";
        @file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($err_log));
    }

    echo "<script>alert('TRANSACCIÓN_FALLIDA: $error_desc'); window.location.href='deposito.php';</script>";
}
?>
