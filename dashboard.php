<?php
// dashboard.php - ESTRUCTURA PLANA
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
$meta = (isset($user['meta_objetivo']) && $user['meta_objetivo'] > 0) ? $user['meta_objetivo'] : 1000;
$porcentaje_progreso = min(100, ($saldo_bloqueado / $meta) * 100);

// Imagen de perfil
$foto_src = "default.png"; // Ajustado a raíz
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
    <title>NEXUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #020617;
            --card-bg: #0f172a;
            --nexus-blue: #0ea5e9;
            --nexus-purple: #8b5cf6;
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
        }

        body { 
            background-color: var(--bg); 
            color: var(--text-main); 
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.15), transparent 50%);
            margin: 0;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }

        /* SIDEBAR */
        .sidebar { 
            width: 260px; height: 100vh; background: #070a19; 
            position: fixed; border-right: 1px solid var(--border); 
            backdrop-filter: blur(20px); z-index: 1000;
        }

        .nav-link { 
            color: var(--text-dim); padding: 12px 20px; border-radius: 10px; 
            margin: 2px 15px; transition: 0.3s; font-size: 0.8rem; font-weight: 600;
        }

        .nav-link:hover, .nav-link.active { 
            background: rgba(14, 165, 233, 0.1); color: var(--nexus-blue) !important; 
        }

        .content { margin-left: 260px; padding: 40px; }

        /* TARJETAS */
        .stat-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 24px; padding: 25px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4);
        }

        .stat-card h1 { color: #ffffff; text-shadow: 0 0 15px rgba(14, 165, 233, 0.3); }

        /* BOTONERA */
        .btn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }

        .action-item {
            background: #1e293b;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px 10px;
            text-align: center;
            text-decoration: none;
            color: var(--text-dim);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-item:hover {
            border-color: var(--nexus-blue);
            background: rgba(14, 165, 233, 0.1);
            color: white;
            transform: translateY(-5px);
        }

        .action-item i { font-size: 1.5rem; margin-bottom: 10px; }
        .action-item span { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }

        .progress { height: 6px; background: rgba(255,255,255,0.05); border-radius: 10px; }
        .progress-bar { background: linear-gradient(90deg, var(--nexus-blue), var(--nexus-purple)); }
        
        .badge-nexus {
            background: rgba(14, 165, 233, 0.1);
            color: var(--nexus-blue);
            border: 1px solid rgba(14, 165, 233, 0.2);
        }

        /* ANIMACIONES LIVE FEED */
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fade-in { animation: fadeInRight 0.5s ease-out forwards; }
        .blink { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
        .pulse-icon { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="py-4 px-4 text-center">
        <h4 class="mono fw-800 mb-0" style="letter-spacing: -1px; color: white;">NEXUS<span class="text-info">_OS</span></h4>
    </div>

    <div class="px-4 mb-4">
        <div class="d-flex align-items-center p-2 bg-white bg-opacity-5 rounded-4 border border-white border-opacity-10">
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
            <h2 class="fw-800 mb-0" style="color: white;">CORE_<span class="text-info">INTERFACE</span></h2>
            <p class="text-secondary small mono">System Status: <span class="text-success">Operational</span> // v2.0.4</p>
        </div>
        <div class="text-end">
            <span class="badge badge-nexus px-3 py-2 rounded-pill mono" style="font-size: 10px;">
                <i class="fa-solid fa-lock me-1"></i> SSL_ENCRYPTED
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="stat-card mb-4">
                <div class="d-flex justify-content-between">
                    <p class="text-dim small fw-bold mb-0 mono">TOTAL_ASSETS_VALUE</p>
                    <i class="fa-solid fa-microchip text-info opacity-75"></i>
                </div>
                <h1 class="fw-800 mt-2">$<?php echo number_format($saldo_bloqueado, 2); ?> <span style="color: var(--text-dim); font-size: 1.2rem;">MXN</span></h1>
                
                <div class="mt-4 pt-3 border-top border-white border-opacity-10">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-dim mono">MINING_PROGRESS</span>
                        <span class="small text-info fw-bold"><?php echo round($porcentaje_progreso); ?>%</span>
                    </div>
                    <div class="progress"><div class="progress-bar" style="width: <?php echo $porcentaje_progreso; ?>%"></div></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <p class="text-dim small fw-bold mb-1 mono">REFERRAL_BALANCE</p>
                        <h4 class="fw-bold text-success mb-0">$<?php echo number_format($user['saldo_referidos'] ?? 0, 2); ?></h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <p class="text-dim small fw-bold mb-1 mono">DAILY_YIELD</p>
                        <h4 class="fw-bold text-info mb-0">+$<?php echo number_format($saldo_bloqueado * 0.0005, 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card h-100" style="background: rgba(14, 165, 233, 0.03); overflow: hidden;">
                <h6 class="mono text-info fw-bold mb-4">
                    <i class="fa-solid fa-rss me-2 pulse-icon"></i>LIVE_WITHDRAWALS
                </h6>
                
                <div id="withdrawal-feed" class="mono" style="font-size: 10px; height: 300px; overflow-y: hidden;">
                    </div>

                <hr class="border-white border-opacity-10">
                
                <div class="p-3 bg-black bg-opacity-40 rounded-4 border border-white border-opacity-5">
                    <p class="text-dim mb-0 mono" style="font-size: 9px;">
                        <span class="text-success blink">●</span> Red Nexus verificando transacciones...
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

<script>
    const feed = document.getElementById('withdrawal-feed');
    const names = ['User_772', 'Nexus_King', 'Crypto_MX', 'Alpha_Node', 'Dev_Jesus', 'Maria_99', 'Ghost_Rider', 'VIP_Trader', 'Zero_Cool', 'Neo_2026'];
    const amounts = [1500, 2400, 850, 5000, 1200, 3100, 450, 9800, 750, 4200];

    function addWithdrawal() {
        const name = names[Math.floor(Math.random() * names.length)];
        const amount = amounts[Math.floor(Math.random() * amounts.length)];
        const time = new Date().toLocaleTimeString();

        const entry = document.createElement('div');
        entry.className = 'mb-3 p-2 border-start border-info border-2 bg-white bg-opacity-5 rounded-end animate-fade-in';
        entry.innerHTML = `
            <div class="d-flex justify-content-between">
                <span class="text-info fw-bold">${name}</span>
                <span class="text-dim" style="font-size: 8px;">${time}</span>
            </div>
            <div class="text-success fw-bold">+$${amount.toLocaleString()} MXN <span class="text-white opacity-50" style="font-size: 8px;">COMPLETED</span></div>
        `;

        feed.prepend(entry);

        if (feed.children.length > 5) {
            feed.removeChild(feed.lastChild);
        }
    }

    function loop() {
        addWithdrawal();
        setTimeout(loop, Math.random() * (8000 - 4000) + 4000);
    }

    loop();
</script>

</body>
</html>
