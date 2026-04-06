<?php
// procesar_login.php - ESTRUCTURA PLANA (RAÍZ)
session_start();
require_once 'config.php'; // Asegúrate de que config.php esté en la misma carpeta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Limpiar entradas (Usamos 'email' del formulario pero buscaremos en 'correo')
    $email_input = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];

    // 2. Buscar al usuario - CORREGIDO SEGÚN TU CAPTURA DE PANTALLA
    // Cambié 'email' por 'correo'. 
    // Quité 'usuarios' y 'verificado' porque no existen en tu tabla.
    $sql = "SELECT id, nombre, password, mi_codigo_referido, telefono FROM usuarios WHERE correo = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 3. Verificar contraseña (BCRYPT)
            if (password_verify($password_input, $user['password'])) {
                
                // --- LOGIN EXITOSO ---
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre']  = $user['nombre'];
                $_SESSION['codigo']  = $user['mi_codigo_referido'];
                // Nota: No hay columna 'verificado' en tu DB, así que asignamos 'si' por defecto
                $_SESSION['status']  = 'si'; 

                // Notificación opcional (Solo si tienes definida la función notifyTelegram en config.php)
                if (function_exists('notifyTelegram')) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $msg = "🔐 [NEXUS LOGIN] ACCESO AUTORIZADO\n";
                    $msg .= "👤 Usuario: " . $user['nombre'] . "\n";
                    $msg .= "📧 Email: $email_input\n";
                    $msg .= "🌐 IP: $ip\n";
                    $msg .= "⏰ Fecha: " . date('Y-m-d H:i:s');
                    notifyTelegram($msg);
                }

                // Redirigir al Dashboard en la raíz
                header("Location: dashboard.php");
                exit();

            } else {
                // Contraseña incorrecta
                header("Location: login.php?error=invalid_credentials");
                exit();
            }
        } else {
            // Usuario no encontrado
            header("Location: login.php?error=user_not_found");
            exit();
        }
        $stmt->close();
    } else {
        // Error de SQL (Probablemente nombres de columnas)
        die("Error en la base de datos: " . $conn->error);
    }
    
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
