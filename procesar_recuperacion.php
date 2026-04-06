<?php
// auth/procesar_recuperacion.php
// Ruta corregida: Salir de auth/ e ir a includes/config.php
require_once 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitización usando la variable $conn de tu config.php
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // 1. Verificar si el usuario existe
    $query = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // 2. Generar Token de 6 dígitos y tiempo de expiración
        $token = rand(100000, 999999);
        $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 3. Guardar en la base de datos
        $update = "UPDATE usuarios SET 
                   token_recuperacion = '$token', 
                   token_expira = '$expira' 
                   WHERE email = '$email'";
        
        if (mysqli_query($conn, $update)) {
            // REDIRECCIÓN CORRECTA: Ambos están en la carpeta 'auth'
            // De procesar_recuperacion.php a confirmar_token.php
            header("Location: confirmar_token.php?email=" . urlencode($email));
            exit();
        }
    } else {
        // REDIRECCIÓN CORRECTA: Salir de 'auth' para ir a 'public/recuperar_contraseña.php'
        header("Location: recuperar_contraseña.php?status=error");
        exit();
    }
} else {
    header("Location: recuperar_contraseña.php");
    exit();
}
?>
