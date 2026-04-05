<?php
// Ruta corregida: Salimos de 'public' para entrar a 'includes'
require '../includes/config.php'; 
session_start();

// Si el usuario no está logueado, regresamos al login (asumiendo que está en la raíz o en public)
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}
$user_id = $_SESSION['user_id'];

// 1. Consultar datos del usuario
$res = $conn->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $res->fetch_assoc();

// 2. Contar referidos con depósito (Activos)
$stmt_ref = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE referido_por_id = ? AND saldo_congelado > 0");
$stmt_ref->bind_param("i", $user_id);
$stmt_ref->execute();
$total_referidos_activos = $stmt_ref->get_result()->fetch_assoc()['total'];

// 3. Configuración de Metas
$meta_referidos = 5; 
$porcentaje_ref = min(100, ($total_referidos_activos / $meta_referidos) * 100);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misiones | NEXUS CORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg: #050505;
            --nexus-blue: #3b82f6;
            --nexus-green: #10b981;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            background-color: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% -20%, rgba(59, 130, 246, 0.15), transparent 50%);
            min-height: 100vh;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }

        .mission-card-glass {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mission-card-glass:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--nexus-blue);
            transform: translateY(-5px);
        }

        .promo-tag {
            background: linear-gradient(90deg, #f59e0b, #ef4444);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 50px;
            text-transform: uppercase;
            margin-bottom: 10px;
            display: inline-block;
        }

        .progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
        }
        .progress-bar {
            background: linear-gradient(90deg, var(--nexus-blue), var(--nexus-green));
            border-radius: 50px;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }

        .btn-nexus-main {
            background: linear-gradient(135deg, var(--nexus-blue), var(--nexus-green));
            color: #000;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            padding: 12px 25px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline-nexus {
            border: 1px solid var(--glass-border);
            background: var(--glass);
            color: #fff;
            border-radius: 12px;
            padding: 8px 20px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
        }

        .status-pill {
            font-size: 10px;
            padding: 4px 12px;
            border-radius: 50px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--nexus-green);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 800px;">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 text-white mb-0" style="letter-spacing: -2px; font-size: 2.5rem;">NEXUS <span style="color: var(--nexus-blue);">REWARDS</span></h1>
            <p class="mono small text-muted opacity-50">LOCATION: /PUBLIC/MISIONES.PHP</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn-outline-nexus text-uppercase" style="font-size: 0.7rem;">Dashboard</a>
            <a href="logout.php" class="btn-outline-nexus text-uppercase" style="font-size: 0.7rem;">Logout</a>
        </div>
    </div>

    <div class="mission-card-glass mb-5" style="border-left: 4px solid var(--nexus-blue);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="status-pill mb-3 d-inline-block">Embajador Nexus</span>
                <h3 class="fw-800 text-white">Gestión de Red</h3>
                <p class="text-muted small">Panel de control de dividendos por invitación.</p>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between mono small mb-2">
                        <span>Sincronización</span>
                        <span class="text-white"><?php echo $total_referidos_activos; ?> / <?php echo $meta_referidos; ?></span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar" style="width: <?php echo $porcentaje_ref; ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="fas fa-project-diagram fa-5x opacity-10" style="color: var(--nexus-blue);"></i>
            </div>
        </div>
        <button class="btn-nexus-main w-100 mt-4 mono" onclick="copyRef()">
            <i class="fas fa-link me-2"></i> COPIAR_INVITACIÓN
        </button>
    </div>

    <h6 class="mono text-muted mb-4">LOGROS_DISPONIBLES:</h6>

    <div class="mission-card-glass">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="promo-tag">Bono de Inicio</span>
                <h5 class="fw-800 text-white mb-1">INYECCIÓN DE CAPITAL X2</h5>
                <p class="text-muted small m-0">"En NEXUS, tu dinero no descansa, se multiplica. Completa tu primera conexión (depósito) y mira cómo tu red comienza a generar dividendos hoy mismo."</p>
            </div>
            <div class="ms-3">
                <?php if($user['saldo_congelado'] > 0): ?>
                    <span class="status-pill">COMPLETADO</span>
                <?php else: ?>
                    <a href="deposito.php" class="btn-nexus-main py-2 px-4" style="font-size: 0.8rem;">DEPOSITAR</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mission-card-glass">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="promo-tag" style="background: var(--nexus-blue);">Comisión 10%</span>
                <h5 class="fw-800 text-white mb-1">DIVIDENDOS POR INVITACIÓN</h5>
                <p class="text-muted small m-0">Gana el 10% de cada carga de saldo realizada por tus referidos.</p>
            </div>
            <div class="text-end">
                <span class="d-block h4 fw-800 mb-0" style="color: var(--nexus-blue);"><?php echo $total_referidos_activos; ?></span>
                <span class="mono text-muted" style="font-size: 9px;">ACTIVOS</span>
            </div>
        </div>
    </div>

</div>

<script>
function copyRef() {
    // Genera el link apuntando a registro.php en la misma carpeta public
    const url = window.location.origin + "/public/registro.php?ref=<?php echo $user['codigo_referido']; ?>";
    navigator.clipboard.writeText(url).then(() => {
        alert("SISTEMA: Enlace copiado. ¡Comparte y gana!");
    });
}
</script>

</body>
</html>
