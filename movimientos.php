<?php
// Salimos de public/ para entrar a includes/
require 'config.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta extendida para traer transacciones (Depósitos y Referidos)
$logs = $conn->query("SELECT * FROM log_transacciones WHERE usuario_id = $user_id ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial | NEXUS CORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg: #050505;
            --nexus-blue: #3b82f6;
            --nexus-purple: #8b5cf6;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            background-color: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% -20%, rgba(139, 92, 246, 0.1), transparent 50%);
            min-height: 100vh;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }

        .nexus-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            border-color: var(--glass-border);
            vertical-align: middle;
        }

        .type-pill {
            font-size: 10px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .pill-deposit { background: rgba(59, 130, 246, 0.1); color: var(--nexus-blue); border: 1px solid var(--nexus-blue); }
        .pill-referral { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }

        .btn-nexus-outline {
            border: 1px solid var(--glass-border);
            color: #94a3b8;
            border-radius: 12px;
            padding: 10px 20px;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-nexus-outline:hover {
            border-color: var(--nexus-blue);
            color: #fff;
            background: rgba(59, 130, 246, 0.05);
        }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 900px;">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 text-white mb-0" style="letter-spacing: -1px;">HISTORIAL <span style="color: var(--nexus-purple);">LOGS</span></h1>
            <p class="mono small text-muted opacity-50">AUDIT_TRACE // USER_ID: <?php echo str_pad($user_id, 4, "0", STR_PAD_LEFT); ?></p>
        </div>
        <a href="dashboard.php" class="btn-nexus-outline mono small text-uppercase">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>

    <div class="nexus-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-muted mono small" style="border-bottom: 2px solid var(--glass-border);">
                        <th>FECHA</th>
                        <th>CONCEPTO</th>
                        <th>TIPO</th>
                        <th class="text-end">MONTO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($logs->num_rows > 0): ?>
                        <?php while($l = $logs->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                <td class="mono small"><?php echo date('d M, Y H:i', strtotime($l['fecha'])); ?></td>
                                <td>
                                    <span class="fw-600 d-block text-white" style="font-size: 0.9rem;">
                                        <?php echo ($l['tipo'] == 'referido') ? 'Bonificación de Red' : 'Depósito de Capital'; ?>
                                    </span>
                                    <span class="text-muted" style="font-size: 0.7rem;">ID_TRANS: #<?php echo $l['id']; ?></span>
                                </td>
                                <td>
                                    <?php if($l['tipo'] == 'referido'): ?>
                                        <span class="type-pill pill-referral">NODO_REF</span>
                                    <?php else: ?>
                                        <span class="type-pill pill-deposit">INYECCIÓN</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-800 <?php echo ($l['monto'] >= 0) ? 'text-success' : 'text-danger'; ?>" style="font-size: 1.1rem;">
                                    <?php echo ($l['monto'] >= 0) ? '+' : '-'; ?>$<?php echo number_format(abs($l['monto']), 2); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted mono small">
                                <i class="fas fa-database mb-3 d-block opacity-20 fa-2xl"></i>
                                NO_DATA_FOUND: No se registraron transacciones activas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center">
        <p class="text-muted" style="font-size: 0.7rem;">
            <i class="fas fa-shield-alt me-1"></i> 
            Los registros mostrados están firmados digitalmente por el protocolo NEXUS.OS
        </p>
    </div>
</div>

</body>
</html>
