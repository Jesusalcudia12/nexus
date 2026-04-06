<?php
// includes/update_profile.php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $file = $_FILES['foto_perfil'];

    if ($file['error'] === 0) {
        $filesize = $file['size'];
        $filetype = $file['type'];
        
        // Validar tamaño (Máximo 1MB para no saturar la DB)
        if ($filesize <= 1 * 1024 * 1024) {
            
            // Leer el contenido del archivo en binario
            $img_content = file_get_contents($file['tmp_name']);

            // Actualizar la base de datos usando sentencias preparadas (BLOB)
            $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            // "b" indica que el parámetro es un blob
            $stmt->bind_param("bi", $null, $user_id);
            $stmt->send_long_data(0, $img_content);

            if ($stmt->execute()) {
                header("Location: perfil.php?status=success");
            } else {
                header("Location: perfil.php?status=error_db");
            }
        } else {
            header("Location: perfil.php?status=too_large");
        }
    } else {
        header("Location: perfil.php?status=error_file");
    }
}
