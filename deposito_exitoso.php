<?php
// deposito_exitoso.php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Obtenemos el monto de la URL para mostrarlo en el recibo
$monto_mostrar = isset($_GET['m']) ? floatval($_GET['m']) : 0;

// Consultamos los datos frescos del usuario para mostrar el saldo real tras el proceso
$stmt = $conn->prepare("SELECT nombre, saldo_congelado, verificado FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Generamos un ID de operación único para el recibo visual
$op_id = "NX-" . date('Ymd') . "-" . strtoupper(substr(md5($user_id . time()), 0, 6));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSACTION_SUCCESS | NEXUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;800&display=swap');
        
        :root {
            --bg: #020617;
            --nexus-green: #10b981;
            --nexus-blue: #3b82f6;
            --glass: rgba(15, 23, 42, 0.9);
            --border: rgba(255, 255, 255, 0.08);
        }

        body { 
            background: var(--bg);
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.08), transparent 70%);
        }

        .receipt-card {
            width: 100%;
            max-width: 400px;
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 40px;
            backdrop-filter: blur(20px);
            text-align: center;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
        }

        .success-icon {
            width: 70px;
            height: 70px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--nexus-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 25px;
            border: 1px solid rgba(16, 185, 129, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0px rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0px rgba(16, 185, 129, 0); }
        }

        .amount {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .status-badge {
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 2px;
            padding: 6px 15px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--nexus-green);
            border-radius: 50px;
            text-transform: uppercase;
        }

        .details-box {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px dashed rgba(255,255,255,0.1);
            text-align: left;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.8rem;
        }

        .label { color: #64748b; font-family: 'JetBrains Mono', monospace; }
        .val { color: #f1f5f9; font-weight: 600; }

        .footer-note {
            background: rgba(255,255,255,0.03);
            border-radius: 15px;
            padding: 15px;
            font-size: 0.65rem;
            color: #475569;
            margin-top: 25px;
            line-height: 1.4;
        }

        .btn-nexus {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            background: white;
            color: black;
            font-weight: 800;
            text-decoration: none;
            display: block;
            margin-top: 25px;
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .btn-nexus:hover {
            transform: translateY(-3px);
            background: #e2e8f0;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <div class="success-icon">
            <i class="fas fa-check-double"></i>
        </div>

        <div class="amount">$<?php echo number_format($monto_mostrar, 2); ?></div>
        <div class="status-badge">Payment_Verified</div>

        <div class="details-box">
            <div class="detail-item">
                <span class="label">OP_ID:</span>
                <span class="val text-info"><?php echo $op_id; ?></span>
            </div>
            <div class="detail-item">
                <span class="label">ACCOUNT:</span>
                <span class="val"><?php echo explode(' ', $user['nombre'])[0]; ?></span>
            </div>
            <div class="detail-item">
                <span class="label">NETWORK:</span>
                <span class="val text-success">MAINNET_NEXUS</span>
            </div>
            <div class="detail-item">
                <span class="label">TIMESTAMP:</span>
                <span class="val small"><?php echo date('d.m.Y / H:i:s'); ?></span>
            </div>
        </div>

        <div class="footer-note">
            <i class="fas fa-shield-alt me-1"></i> 
            Este cargo aparecerá en su banco como <strong>"SERVICIOS DIGITALES"</strong> o <strong>"NEX_RECHARGE"</strong>. Su saldo total se ha actualizado en la caja turbo.
        </div>

        <a href="dashboard.php" class="btn-nexus">Finalizar Sesión</a>
        
        <p class="mt-4 mb-0 opacity-25 small mono" style="font-size: 8px; letter-spacing: 2px;">ENCRYPTED_NEXUS_PROTOCOL</p>
    </div>

</body>
</html>
