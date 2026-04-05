<?php
// auth/actualizar_password.php
require_once '../../includes/config.php'; // Ruta corregida hacia la carpeta includes
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['email_reset'])) {
    $email = $_SESSION['email_reset'];
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    // 1. Validar coincidencia
    if ($pass1 !== $pass2) {
        header("Location: ../nueva_contraseña.php?error=mismatch");
        exit();
    }

    // 2. Validar fortaleza (Mismas reglas que en el registro)
    if (strlen($pass1) < 8 || !preg_match("/[A-Z]/", $pass1) || !preg_match("/[0-9]/", $pass1)) {
        header("Location: ../nueva_contraseña.php?error=weak");
        exit();
    }

    // 3. Encriptación de Grado Industrial (BCRYPT)
    $password_encriptada = password_hash($pass1, PASSWORD_BCRYPT);

    // 4. Actualizar base de datos y limpiar tokens de seguridad
    // Usamos $conn y la columna 'email' según tu estructura previa
    $sql = "UPDATE usuarios SET 
            password = '$password_encriptada', 
            token_recuperacion = NULL, 
            token_expira = NULL 
            WHERE email = '$email'";
    
    if (mysqli_query($conn, $sql)) {
        // Limpiar la sesión de recuperación
        unset($_SESSION['email_reset']);
        
        // Notificación de éxito con estilo Nexus
        echo "<script>
                alert('Protocolo de seguridad completado: Contraseña actualizada en NEXUS.OS'); 
                window.location.href='../login.php?reset=success';
              </script>";
    } else {
        echo "<script>alert('Error en el nodo de datos. Intente de nuevo.'); window.history.back();</script>";
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>
