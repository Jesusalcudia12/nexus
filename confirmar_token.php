<?php
// auth/confirmar_token.php
require_once 'config.php'; // Ruta corregida a includes
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $token_ingresado = mysqli_real_escape_string($conn, $_POST['token']);
    $ahora = date('Y-m-d H:i:s');

    // Buscamos al usuario con el email, token y que no haya expirado (15 min)
    $query = "SELECT id FROM usuarios 
              WHERE email = '$email' 
              AND token_recuperacion = '$token_ingresado' 
              AND token_expira > '$ahora' 
              LIMIT 1";
    
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Token correcto: Guardamos el email para el cambio final
        $_SESSION['email_reset'] = $email;
        // Salir de auth/ para ir a public/nueva_contraseña.php
        header("Location: nueva_contraseña.php");
        exit();
    } else {
        // Token incorrecto o expirado: Regresar a la interfaz de ingreso de código
        header("Location: confirmar_token_view.php?email=" . urlencode($email) . "&error=invalido");
        exit();
    }
}
?>
