<?php
// auth/procesar_registro.php
include 'config.php'; // Incluimos la configuración unificada

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización de entradas
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $pass1  = $_POST['pass1'];
    $pass2  = $_POST['pass2'];
    $referido_por = mysqli_real_escape_string($conn, $_POST['referido_por']);

    // 1. Validar que las contraseñas coincidan
    if ($pass1 !== $pass2) {
        header("Location: registro.php?error=password_mismatch");
        exit();
    }

    // 2. REGLAS PARA LA CONTRASEÑA (Seguridad NEXUS)
    // Mínimo 8 caracteres, al menos una letra mayúscula y un número
    if (strlen($pass1) < 8 || !preg_match("/[A-Z]/", $pass1) || !preg_match("/[0-9]/", $pass1)) {
        header("Location: registro.php?error=password_weak");
        exit();
    }

    // 3. Generar Código de Referido Único para el nuevo usuario
    $mi_codigo = "NX" . date('y') . "-" . rand(1000, 9999);

    // 4. Encriptar contraseña
    $password_hash = password_hash($pass1, PASSWORD_BCRYPT);

    // 5. Insertar en la Base de Datos
    $sql = "INSERT INTO usuarios (nombre, email, password, mi_codigo_referido, referido_por_codigo, verificado) 
            VALUES (?, ?, ?, ?, ?, 'no')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $email, $password_hash, $mi_codigo, $referido_por);

    if ($stmt->execute()) {
        // FUNCIÓN DE NOTIFICACIÓN A TELEGRAM ELIMINADA

        // Redirigir al login con mensaje de éxito
        header("Location: login.php?registro=success");
    } else {
        // Error de base de datos (Ej: email duplicado)
        header("Location: registro.php?error=db_error");
    }
    
    $stmt->close();
    $conn->close();
}
?>
