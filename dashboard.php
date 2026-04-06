<?php
// includes/dashboard.php
require 'config.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta de datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lógica de progreso (Simulación de meta)
$saldo_bloqueado = $user['saldo_congelado'] ?? 0;
$meta = ($user['meta_objetivo'] > 0) ? $user['meta_objetivo'] : 1;
$porcentaje_progreso = min(100, ($saldo_bloqueado / $meta) * 100);

// Imagen de perfil
$foto_src = "../public/img/default.png"; 
if (!empty($user['foto_perfil'])) {
    $base64 = base64_encode($user['foto_perfil']);
    $foto_src = 'data:image/jpeg;base64,' . $base64;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TERMINAL | NEXUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #020617;
            --nexus-blue: #0ea5e9;
            --nexus-purple: #8b5cf6;
            --glass: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.08);
        }

        body { 
            background-color: var(--bg); 
            color: #f8fafc; 
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.1), transparent 50%);
        }

        .mono { font-family: 'JetBrains Mono', monospace; }

        /* SIDEBAR COMPLETO */
        .sidebar { 
            width: 260px; height: 100vh; background: rgba(7, 10, 25, 0.95); 
            position: fixed; border-right: 1px solid var(--border); 
            backdrop-filter: blur(20px); z-index: 1000;
        }

        .nav-link { 
            color: rgba(255,255,255,0.5); padding: 10px 20px; border-radius: 10px; 
            margin: 2px 15px; transition: 0.2s; font-size: 0.75rem; font-weight: 600;
        }

        .nav-link:hover, .nav-link.active { 
            background: rgba(14, 165, 233, 0.1); color: var(--nexus-blue); 
        }

        .content { margin-left: 260px; padding: 40px; }

        /* TARJETAS */
        .stat-card { 
            background: var(--glass); border: 1px solid var(--border); 
            border-radius: 24px; padding: 25px; transition: 0.3s;
        }

        /* BOTONERA EXPANDIDA */
        .btn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 12px;
            margin-top: 30px;
        }

        .action-item {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px 10px;
            text-align: center;
            text-decoration: none;
            color: #94a3b8;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-item:hover {
            border-color: var(--nexus-blue);
            background: rgba(14, 165, 233, 0.05);
            color: white;
            transform: translateY(-5px);
        }

        .action-item i { font-size: 1.4rem; margin-bottom: 10px; }
        .action-item span { font-size: 0.65rem; font-weight: 800; letter-spacing: 0.5px; }

        .progress { height: 4px; background: rgba(255,255,255,0.05); border-radius: 10px; }
        .progress-bar { background: linear-gradient(90deg, var(--nexus-blue), var(--nexus-purple)); }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="py-4 px-4 text-center">
        <h4 class="mono fw-800 mb-0" style="letter-spacing: -1px;">NEXUS<span class="text-info">_OS</span></h4>
    </div>

    <div class="px-4 mb-4">
        <div class="d-flex align-items-center p-2 bg-white bg-opacity-5 rounded-4">
            <img src="<?php echo $foto_src; ?>" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover;" class="me-2">
            <div style="overflow: hidden;">
                <p class="mb-0 small fw-bold text-white text-truncate"><?php echo $user['nombre']; ?></p>
                <span class="text-info mono" style="font-size: 9px;">ACTIVE_NODE</span>
            </div>
        </div>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link active" href="dashboard.php"><i class="fa-solid fa-layer-group me-2"></i> DASHBOARD</a>
        <a class="nav-link" href="deposito.php"><i class="fa-solid fa-wallet me-2"></i> BILLETERA</a>
        <a class="nav-link" href="resumen.php"><i class="fa-solid fa-chart-line me-2"></i> HISTORIAL</a>
        <a class="nav-link" href="referidos.php"><i class="fa-solid fa-network-wired me-2"></i> RED AFILIADOS</a>
        <a class="nav-link" href="misiones.php"><i class="fa-solid fa-shield-heart me-2"></i> MISIONES</a>
        <a class="nav-link" href="banco.php"><i class="fa-solid fa-university me-2"></i> BANCO</a>
        <a class="nav-link" href="perfil.php"><i class="fa-solid fa-id-card me-2"></i> AJUSTES</a>
        <a class="nav-link" href="soporte.php"><i class="fa-solid fa-headset me-2"></i> SOPORTE</a>
    </nav>

    <div class="mt-auto p-4">
        <a href="logout.php" class="nav-link text-danger m-0 p-2"><i class="fa-solid fa-power-off me-2"></i> LOGOUT</a>
    </div>
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-800 mb-0">CORE_<span class="text-info">INTERFACE</span></h2>
            <p class="text-secondary small mono">System Status: Operational // v2.0.4</p>
        </div>
        <div class="text-end">
            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-3 py-2 rounded-pill mono" style="font-size: 10px;">
                <i class="fa-solid fa-lock me-1"></i> SSL_ENCRYPTED
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="stat-card mb-4">
                <div class="d-flex justify-content-between">
                    <p class="text-secondary small fw-bold mb-0 mono">TOTAL_ASSETS_VALUE</p>
                    <i class="fa-solid fa-microchip text-info opacity-50"></i>
                </div>
                <h1 class="fw-800 mt-2">$<?php echo number_format($saldo_bloqueado, 2); ?> <span class="text-muted h5">MXN</span></h1>
                
                <div class="mt-4 pt-3 border-top border-white border-opacity-5">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-secondary mono">MINING_PROGRESS</span>
                        <span class="small text-info fw-bold"><?php echo round($porcentaje_progreso); ?>%</span>
                    </div>
                    <div class="progress"><div class="progress-bar" style="width: <?php echo $porcentaje_progreso; ?>%"></div></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <p class="text-secondary small fw-bold mb-1 mono">REFERRAL_BALANCE</p>
                        <h4 class="fw-bold text-success mb-0">$<?php echo number_format($user['saldo_referidos'], 2); ?></h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <p class="text-secondary small fw-bold mb-1 mono">DAILY_YIELD</p>
                        <h4 class="fw-bold text-info mb-0">+$<?php echo number_format($saldo_bloqueado * 0.0005, 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card h-100 bg-info bg-opacity-5">
                <h6 class="mono text-info fw-bold mb-4"><i class="fa-solid fa-server me-2"></i>NODE_INFO</h6>
                <div class="mb-3">
                    <small class="text-muted d-block mono" style="font-size: 10px;">ACCESS_KEY</small>
                    <span class="text-white small fw-bold">#<?php echo $user['codigo_referido']; ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mono" style="font-size: 10px;">SERVER_LOCATION</small>
                    <span class="text-white small fw-bold">SAN_JOSE_CA</span>
                </div>
                <hr class="border-white border-opacity-10">
                <div class="p-3 bg-black bg-opacity-30 rounded-4">
                    <p class="text-muted mb-0 mono" style="font-size: 9px; line-height: 1.5;">
                        > Optimización de hardware activa.<br>
                        > Protocolo de minería: NEX-V2.<br>
                        > Retiros habilitados: SI.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="btn-grid">
        <a href="deposito.php" class="action-item">
            <i class="fa-solid fa-plus-circle text-info"></i>
            <span>INVERTIR</span>
        </a>
        <a href="retiro.php" class="action-item">
            <i class="fa-solid fa-arrow-up-from-bracket text-success"></i>
            <span>RETIRAR</span>
        </a>
        <a href="resumen.php" class="action-item">
            <i class="fa-solid fa-clock-rotate-left text-info"></i>
            <span>HISTORIAL</span>
        </a>
        <a href="banco.php" class="action-item">
            <i class="fa-solid fa-building-columns text-secondary"></i>
            <span>BANCO</span>
        </a>
        <a href="referidos.php" class="action-item">
            <i class="fa-solid fa-users text-primary"></i>
            <span>MI RED</span>
        </a>
        <a href="misiones.php" class="action-item">
            <i class="fa-solid fa-award text-warning"></i>
            <span>MISIONES</span>
        </a>
        <a href="perfil.php" class="action-item">
            <i class="fa-solid fa-user-cog text-info"></i>
            <span>PERFIL</span>
        </a>
        <a href="soporte.php" class="action-item">
            <i class="fa-solid fa-comment-dots text-white"></i>
            <span>SOPORTE</span>
        </a>
    </div>
</div>

</body>
</html>
