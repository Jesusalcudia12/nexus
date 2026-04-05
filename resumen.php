<?php
require '../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = "";

// 1. LÓGICA DE TRANSFERENCIA INTERNA (P2P)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transferir'])) {
    $monto = floatval($_POST['monto']);
    $destinatario_cod = mysqli_real_escape_string($conn, $_POST['codigo_destino']);
    
    // Verificar si el usuario tiene saldo suficiente en referidos (que es el saldo líquido)
    $check = $conn->query("SELECT saldo_referidos FROM usuarios WHERE id = $user_id")->fetch_assoc();
    
    if ($monto <= 0) {
        $mensaje = "<div class='alert alert-danger bg-opacity-10 border-danger small mono'>ERROR_INVALID_AMOUNT</div>";
    } elseif ($check['saldo_referidos'] < $monto) {
        $mensaje = "<div class='alert alert-danger bg-opacity-10 border-danger small mono'>ERROR_INSUFFICIENT_FUNDS</div>";
    } else {
        // Buscar destinatario
        $dest = $conn->query("SELECT id FROM usuarios WHERE codigo_referido = '$destinatario_cod'")->fetch_assoc();
        
        if ($dest) {
            $dest_id = $dest['id'];
            // Proceso de transferencia (Transacción simple)
            $conn->query("UPDATE usuarios SET saldo_referidos = saldo_referidos - $monto WHERE id = $user_id");
            $conn->query("UPDATE usuarios SET saldo_referidos = saldo_referidos + $monto WHERE id = $dest_id");
            
            // Registrar en historial (opcional si tienes tabla de logs)
            $mensaje = "<div class='alert alert-success bg-opacity-10 border-success small mono'>TRANSFER_SUCCESSFUL_TO_#$destinatario_cod</div>";
        } else {
            $mensaje = "<div class='alert alert-danger bg-opacity-10 border-danger small mono'>ERROR_NODE_NOT_FOUND</div>";
        }
    }
}

// 2. CONSULTA DE DATOS ACTUALIZADOS
$user = $conn->query("SELECT * FROM usuarios WHERE id = $user_id")->fetch_assoc();
$saldo_inv = $user['saldo_congelado'] ?? 0;
$saldo_ref = $user['saldo_referidos'] ?? 0;
$ganancia_anual = $saldo_inv * 0.18; // 18% Anual
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Activos | NEXUS.OS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --cyan: #06b6d4; --purple: #8b5cf6; --glass: rgba(255, 255, 255, 0.03); }
        body { background: var(--bg); color: #f1f5f9; font-family: 'Inter', sans-serif; min-height: 100vh; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .nexus-card { background: var(--glass); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 25px; backdrop-filter: blur(10px); }
        
        /* Tarjeta de Crédito Difuminada */
        .credit-card-preview {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            height: 200px;
            width: 100%;
            max-width: 350px;
            border-radius: 20px;
            position: relative;
            margin: 20px auto;
            filter: blur(4px); /* Difuminado pedido */
            opacity: 0.6;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .credit-title { font-size: 3rem; font-weight: 900; letter-spacing: -2px; color: rgba(255,255,255,0.1); text-transform: uppercase; }
        
        .btn-transfer { background: var(--purple); color: white; font-weight: 700; border: none; border-radius: 12px; padding: 12px; transition: 0.3s; }
        .btn-transfer:hover { background: #7c3aed; transform: scale(1.02); }
        
        .input-nexus { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; padding: 10px; font-size: 0.9rem; }
        .input-nexus:focus { border-color: var(--cyan); box-shadow: none; background: rgba(0,0,0,0.6); color: white; }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 900px;">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 mb-0 mono">ASSET_<span class="text-info">SUMMARY</span></h1>
            <p class="text-muted small mb-0">Revisión de activos y movimientos internos</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mono rounded-pill px-3">
            <i class="fas fa-chevron-left me-1"></i> RETURN
        </a>
    </div>

    <?php echo $mensaje; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="nexus-card mb-4 border-info border-opacity-20">
                <p class="text-secondary small fw-bold mono mb-2">SALDO_INVERTIDO_TOTAL</p>
                <h2 class="fw-800">$<?php echo number_format($saldo_inv, 2); ?></h2>
                <div class="mt-3 p-3 bg-info bg-opacity-5 rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small mono text-info">RENDIMIENTO_ANUAL (18%)</span>
                        <span class="fw-bold text-success">+$<?php echo number_format($ganancia_anual, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="nexus-card border-purple border-opacity-20">
                <p class="text-secondary small fw-bold mono mb-2">SALDO_REFERIDOS_LIQUIDO</p>
                <h2 class="fw-800 text-purple" style="color: var(--purple);">$<?php echo number_format($saldo_ref, 2); ?></h2>
                <small class="text-muted mono" style="font-size: 10px;">> Disponible para transferencia inmediata</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="nexus-card h-100">
                <h6 class="mono text-white fw-bold mb-4"><i class="fas fa-exchange-alt me-2 text-info"></i>P2P_TRANSFER</h6>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="small text-muted mono mb-2">DESTINATION_NODE_CODE</label>
                        <input type="text" name="codigo_destino" class="form-control input-nexus mono" placeholder="#NEX-0000" required>
                    </div>
                    <div class="mb-4">
                        <label class="small text-muted mono mb-2">AMOUNT_TO_SEND (MXN)</label>
                        <input type="number" name="monto" step="0.01" class="form-control input-nexus mono" placeholder="0.00" required>
                    </div>
                    <button type="submit" name="transferir" class="btn-transfer w-100 mono">
                        EXECUTE_TRANSFER
                    </button>
                </form>
                <p class="text-center mt-3 text-muted" style="font-size: 9px;">
                    Las transferencias entre cuentas Nexus son instantáneas y sin comisión.
                </p>
            </div>
        </div>

        <div class="col-12 mt-5 text-center">
            <h2 class="fw-900 text-white mb-0" style="letter-spacing: 5px;">CREDITO</h2>
            <div class="credit-card-preview">
                <div class="credit-title mono">NEXUS</div>
                <i class="fas fa-microchip position-absolute" style="top: 20%; left: 10%; font-size: 2rem; color: rgba(255,255,255,0.2);"></i>
            </div>
            <p class="text-info mono fw-bold mt-2" style="letter-spacing: 2px; font-size: 12px;">
                PROXIMAMENTE EN MEXICO
            </p>
        </div>
    </div>

    <div class="mt-5 pt-5 text-center opacity-25">
        <p class="mono small mb-0">NEXUS_CORE_FINANCE // ENCRYPTED_REPORT_<?php echo date('Y'); ?></p>
    </div>
</div>

</body>
</html>
