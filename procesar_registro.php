<?php
// procesar_registro.php - ESTRUCTURA PLANA (RAÍZ)
require 'config.php'; // Incluimos la configuración directa en la raíz
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitización de entradas (Ajustado a los nombres de tu formulario)
    // Usamos isset() para evitar los errores de "Clave indefinida"
    $nombre   = isset($_POST['nombre']) ? mysqli_real_escape_string($conn, $_POST['nombre']) : '';
    $telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($conn, $_POST['telefono']) : '';
    $correo   = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : ''; // Recibe 'email' del form pero guardaremos en 'correo'
    $pass1    = $_POST['pass1'];
    $pass2    = $_POST['pass2'];
    
    // Capturamos referido de la sesión o del post
    $referido_por = isset($_SESSION['ref_code']) ? $_SESSION['ref_code'] : (isset($_POST['referido_por']) ? mysqli_real_escape_string($conn, $_POST['referido_por']) : null);

    // 2. Validar que las contraseñas coincidan
    if ($pass1 !== $pass2) {
        header("Location: registro.php?error=password_mismatch");
        exit();
    }

    // 3. REGLAS PARA LA CONTRASEÑA (Seguridad NEXUS)
    // Mínimo 8 caracteres, al menos una letra mayúscula y un número
    if (strlen($pass1) < 8 || !preg_match("/[A-Z]/", $pass1) || !preg_match("/[0-9]/", $pass1)) {
        header("Location: registro.php?error=password_weak");
        exit();
    }

    // 4. Generar Código de Referido Único (Estilo NEXUS)
    $mi_codigo = "NX" . date('y') . "-" . rand(1000, 9999);

    // 5. Encriptar contraseña
    $password_hash = password_hash($pass1, PASSWORD_BCRYPT);

    // 6. Insertar en la Base de Datos - CORREGIDO PARA TU TABLA REAL
    // Columnas exactas de tu captura: nombre, correo, password, mi_codigo_referido, referido_por_codigo, telefono
    // Nota: He eliminado 'verificado' porque NO aparece en tu captura de phpMyAdmin
    $sql = "INSERT INTO usuarios (nombre, correo, password, mi_codigo_referido, referido_por_codigo, telefono) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // "ssssss" indica 6 strings
        $stmt->bind_param("ssssss", $nombre, $correo, $password_hash, $mi_codigo, $referido_por, $telefono);

        if ($stmt->execute()) {
            // Redirigir al login con mensaje de éxito
            header("Location: login.php?registro=success");
            exit();
        } else {
            // Error de base de datos (Ej: correo o teléfono duplicado)
            header("Location: registro.php?error=db_error");
            exit();
        }
    } else {
        // Error de preparación (Suele ser por nombres de columnas mal escritos)
        die("Error en preparación SQL: " . $conn->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>
