<?php
// auth/procesar_login.php
session_start();
require_once '../config.php'; // Conexión y funciones del bot

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Limpiar entradas para seguridad
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 2. Buscar al usuario en la base de datos de InfinityFree
    $sql = "SELECT id, nombre, password, verificado, mi_codigo_referido FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 3. Verificar si la contraseña coincide (usando BCRYPT)
        if (password_verify($password, $user['password'])) {
            
            // --- LOGIN EXITOSO ---
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre']  = $user['nombre'];
            $_SESSION['codigo']  = $user['mi_codigo_referido'];
            $_SESSION['status']  = $user['verificado'];

            // Alerta a Telegram vía ZENITH TITAN
            $ip = $_SERVER['REMOTE_ADDR'];
            $msg = "🔐 [NEXUS LOGIN] ACCESO AUTORIZADO\n";
            $msg .= "👤 Usuario: " . $user['nombre'] . "\n";
            $msg .= "📧 Email: $email\n";
            $msg .= "🌐 IP: $ip\n";
            $msg .= "⏰ Fecha: " . date('Y-m-d H:i:s');
            
            notifyTelegram($msg);

            // Redirigir al Dashboard
            header("Location: ../dashboard.php");
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: ../login.php?error=user_not_found");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si intentan entrar al archivo directamente sin el formulario
    header("Location: ../login.php");
    exit();
}
?>
