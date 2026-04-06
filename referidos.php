<?php 
require 'config.php'; 
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Obtener código de referido y datos base
$u = $conn->query("SELECT codigo_referido FROM usuarios WHERE id = $user_id")->fetch_assoc();
$my_ref_code = $u['codigo_referido'];

// 2. Contar cantidad de referidos reales (Nodos)
$res_count = $conn->query("SELECT COUNT(*) as total_ref FROM usuarios WHERE referido_por = '$my_ref_code'");
$count_ref = $res_count->fetch_assoc();
$num_referidos = (int)$count_ref['total_ref'];

// 3. Calcular total ganado por comisiones
$ganado = $conn->query("SELECT SUM(monto_comision) as total FROM comisiones WHERE usuario_id = $user_id")->fetch_assoc();

// 4. Calcular el volumen total depositado por los referidos
$volumen = $conn->query("SELECT SUM(saldo_congelado) as total_vol FROM usuarios WHERE referido_por = '$my_ref_code'")->fetch_assoc();
$total_volumen = $volumen['total_vol'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Hub | NEXUS CORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #020617; 
            --nexus-blue: #0ea5e9; 
            --nexus-purple: #8b5cf6;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --success-glow: rgba(16, 185, 129, 0.2);
        }
        body { 
            background-color: var(--bg); 
            color: #f1f5f9; 
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% -20%, rgba(59, 130, 246, 0.15), transparent 50%);
            min-height: 100vh;
        }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .nexus-card { 
            background: var(--glass); 
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px; 
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        .copy-box { 
            background: rgba(0, 0, 0, 0.4); 
            padding: 18px; 
            border-radius: 14px; 
            border: 1px dashed var(--nexus-blue); 
            color: var(--nexus-blue); 
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
        }
        .copy-box:hover { background: rgba(14, 165, 233, 0.1); border-style: solid; }
        
        /* Notificación de Simulación */
        .alert-network {
            background: var(--success-glow);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 16px;
            padding: 20px;
            animation: slideIn 0.5s ease-out, pulse 3s infinite;
        }
        @keyframes slideIn { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.2); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

        .stat-card { transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--nexus-blue); }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 750px;">
    
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="fw-800 mb-0 mono" style="letter-spacing: -2px;">NEXUS_<span class="text-info">NETWORK</span></h1>
            <p class="text-muted small mb-0">NODES_AND_REFERRALS_MONITOR</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mono" style="border-radius: 10px; font-size: 11px;">
            <i class="fas fa-arrow-left me-1"></i> EXIT_TO_DASHBOARD
        </a>
    </div>

    <?php if($num_referidos >= 15): ?>
    <div class="alert-network mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="fas fa-check-double text-white"></i>
                </div>
            </div>
            <div>
                <h6 class="mb-1 fw-800 text-white mono" style="font-size: 13px;">NETWORK_EVENT: TARGET_REACHED</h6>
                <p class="mb-0 text-success small" style="line-height: 1.4;">
                    Uno de tus referidos directos ha completado su ciclo. 
                    Monto retirado: <strong>$10,000.00 MXN</strong>. <br>
                    <span class="text-muted opacity-50 mono" style="font-size: 10px;">TIMESTAMP: <?php echo date('H:i'); ?>_UTC-6</span>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-4 text-center">
        <div class="col-4">
            <div class="nexus-card p-3 stat-card">
                <small class="text-muted d-block mono mb-2" style="font-size: 10px;">ACTIVE_NODES</small>
                <h3 class="fw-800 text-white mb-0"><?php echo $num_referidos; ?></h3>
            </div>
        </div>
        <div class="col-4">
            <div class="nexus-card p-3 stat-card">
                <small class="text-muted d-block mono mb-2" style="font-size: 10px;">TOTAL_REVENUE</small>
                <h3 class="fw-800 text-success mb-0">$<?php echo number_format($ganado['total'] ?? 0, 2); ?></h3>
            </div>
        </div>
        <div class="col-4">
            <div class="nexus-card p-3 stat-card">
                <small class="text-muted d-block mono mb-2" style="font-size: 10px;">VOLUME_IN</small>
                <h3 class="fw-800 text-info mb-0">$<?php echo number_format($total_volumen, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="nexus-card mb-4 text-center border-info border-opacity-10">
        <div class="py-3">
            <i class="fas fa-share-nodes fa-3xl text-info mb-4 opacity-75"></i>
            <h4 class="fw-800">Expande tu Infraestructura</h4>
            <p class="text-muted small px-md-5 mb-4">Comparte tu enlace único. El protocolo Nexus te otorga automáticamente el 10% de cada inyección de capital realizada por tus nodos conectados.</p>
            
            <div class="mx-md-4">
                <div class="copy-box mono small" id="copyBtn" onclick="copyLink()">
                    <span id="refLink">https://nexus-core.com/index.php?ref=<?php echo $my_ref_code; ?></span>
                    <i class="fas fa-copy ms-2 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="nexus-card bg-black bg-opacity-20">
        <h6 class="mono text-info mb-4 fw-bold small"><i class="fas fa-trophy me-2 text-warning"></i>NETWORK_CHALLENGES</h6>
        
        <div class="d-flex align-items-center mb-3 p-3 bg-white bg-opacity-5 rounded-4 border border-white border-opacity-5">
            <div class="stat-icon me-3">
                <i class="fas fa-users-rays text-info fa-xl"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold small text-white uppercase">RECLUTADOR_NIVEL_ALPHA</p>
                <p class="text-muted mb-0" style="font-size: 11px;">Refiere a 20 personas con depósito y desbloquea el <span class="text-info fw-bold">Modo Retiro Prioritario</span>.</p>
            </div>
            <div class="ms-auto">
                <span class="badge bg-dark border border-secondary mono" style="font-size: 9px;"><?php echo $num_referidos; ?>/20</span>
            </div>
        </div>

        <div class="d-flex align-items-center p-3 bg-white bg-opacity-5 rounded-4 border border-white border-opacity-5">
            <div class="stat-icon me-3">
                <i class="fas fa-chart-line text-success fa-xl"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold small text-white uppercase">TURBO_COMMISSION_V3</p>
                <p class="text-muted mb-0" style="font-size: 11px;">Si tu volumen de red supera los $50,000 MXN, tu comisión sube permanentemente al <span class="text-success fw-bold">15%</span>.</p>
            </div>
            <div class="ms-auto">
                 <span class="badge bg-dark border border-secondary mono" style="font-size: 9px;"><?php echo floor(($total_volumen / 50000) * 100); ?>%</span>
            </div>
        </div>
    </div>

    <div class="mt-5 text-center opacity-40">
        <p class="mono mb-0" style="font-size: 9px;">
            <i class="fas fa-shield-halved text-info me-1"></i> 
            SECURE_CONNECTION: AES-GCM_256 // NODE_ID: <?php echo bin2hex($my_ref_code); ?>
        </p>
    </div>
</div>

<script>
function copyLink() {
    const link = document.getElementById('refLink').innerText;
    const btn = document.getElementById('copyBtn');
    
    navigator.clipboard.writeText(link).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="text-success fw-bold mono">LINK_COPIED_SUCCESSFULLY</span> <i class="fas fa-check ms-2"></i>';
        btn.style.borderColor = '#10b981';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.borderColor = '';
        }, 2000);
    });
}
</script>

</body>
</html>
