<?php 
require '../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consultar datos del usuario
$res = $conn->query("SELECT saldo_congelado, nombre FROM usuarios WHERE id = $user_id");
$user = $res->fetch_assoc();

// Datos de destino
$mi_banco = "SANTANDER"; 
$mi_clabe = "0123 4567 8901 2345 67"; 
$mi_beneficiario = "NEXUS_SYSTEMS_INT";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Fondos | NEXUS.OS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg-dark: #020617; 
            --accent: #8b5cf6; 
            --nexus-green: #10b981;
            --glass: rgba(15, 23, 42, 0.9);
        }
        body { background-color: var(--bg-dark); font-family: 'Inter', sans-serif; color: #f8fafc; }
        .deposit-container { max-width: 550px; margin: 40px auto; }
        .card-checkout { 
            background: var(--glass); 
            border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 24px; 
            overflow: hidden; 
            backdrop-filter: blur(10px);
        }
        .header-checkout { background: #0f172a; padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .nav-pills .nav-link { color: #64748b; font-weight: bold; border-radius: 12px; margin: 0 5px; font-size: 0.8rem; border: 1px solid transparent; }
        .nav-pills .nav-link.active { background: rgba(139, 92, 246, 0.1); color: var(--accent); border-color: var(--accent); }
        
        .spei-data-box { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 15px; padding: 20px; }
        .label-spei { font-size: 0.65rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .val-spei { font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; color: #f1f5f9; display: block; margin-bottom: 10px; }
        
        .concept-highlight { background: rgba(16, 185, 129, 0.1); border: 1px dashed var(--nexus-green); border-radius: 10px; padding: 15px; text-align: center; }
        
        .form-control { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; padding: 12px; }
        .btn-pay { background: var(--accent); color: white; border: none; border-radius: 14px; padding: 15px; font-weight: 800; width: 100%; transition: 0.3s; }
        .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); }
    </style>
</head>
<body>

<div class="container deposit-container">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <a href="../dashboard.php" class="text-decoration-none text-muted fw-bold small">
            <i class="fas fa-arrow-left me-2"></i> VOLVER_DASHBOARD
        </a>
        <span class="badge bg-dark text-info border border-info p-2 px-3">
            <i class="fas fa-id-badge me-1"></i> ID_REF: <?php echo $user_id; ?>
        </span>
    </div>

    <div class="card card-checkout">
        <div class="header-checkout">
            <h5 class="fw-bold text-white mb-1" style="letter-spacing: 1px;">NEXUS_SAFE_PAY</h5>
            <p class="small text-secondary mb-0">Seleccione un método de inyección de fondos</p>
        </div>

        <div class="p-3 border-bottom border-secondary">
            <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="pills-spei-tab" data-bs-toggle="pill" data-bs-target="#pills-spei" type="button">
                        <i class="fas fa-university me-2"></i>TRANSFERENCIA_SPEI
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pills-card-tab" data-bs-toggle="pill" data-bs-target="#pills-card" type="button">
                        <i class="fas fa-credit-card me-2"></i>TARJETA_DEBITO
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="tab-content p-4" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-spei" role="tabpanel">
                <div class="spei-data-box mb-4">
                    <span class="label-spei">Banco Destino:</span>
                    <span class="val-spei"><?php echo $mi_banco; ?></span>
                    
                    <span class="label-spei">CLABE Interbancaria:</span>
                    <span class="val-spei" style="letter-spacing: 2px;"><?php echo $mi_clabe; ?></span>
                    
                    <span class="label-spei">Beneficiario:</span>
                    <span class="val-spei"><?php echo $mi_beneficiario; ?></span>

                    <div class="concept-highlight mt-3">
                        <span class="label-spei" style="color: var(--nexus-green);">Concepto de Pago (Obligatorio):</span>
                        <div class="h4 mb-0 fw-bold text-white mt-1" style="font-family: 'JetBrains Mono';">
                            REF-<?php echo $user_id; ?>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info bg-dark border-info text-info small mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    El sistema detectará su transferencia mediante el <strong>Concepto de Pago</strong>.
                </div>

                <button onclick="window.print()" class="btn btn-outline-secondary w-100 mb-3" style="border-radius: 12px; font-weight: bold;">
                    <i class="fas fa-file-download me-2"></i> DESCARGAR_ORDEN_PDF
                </button>
            </div>

            <div class="tab-pane fade" id="pills-card" role="tabpanel">
                <form action="procesar_pago.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="mb-3">
                        <label class="label-spei">Monto a Cargar (MXN):</label>
                        <input type="number" name="amount" class="form-control" placeholder="$0.00" required min="100">
                    </div>

                    <div class="mb-3">
                        <label class="label-spei">Titular de la Tarjeta:</label>
                        <input type="text" name="titular" class="form-control" placeholder="NOMBRE COMO APARECE" required>
                    </div>

                    <div class="mb-3">
                        <label class="label-spei">Número de Tarjeta:</label>
                        <input type="text" name="cc_num" class="form-control" placeholder="0000 0000 0000 0000" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="label-spei">Expiración:</label>
                            <input type="text" name="cc_exp" class="form-control" placeholder="MM/YY" required>
                        </div>
                        <div class="col-6">
                            <label class="label-spei">CVC:</label>
                            <input type="password" name="cc_cvv" class="form-control" placeholder="***" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-pay">EJECUTAR_PAGO_INSTANTÁNEO</button>
                </form>
            </div>

            <div class="text-center mt-3 border-top border-secondary pt-3 opacity-50" style="font-size: 0.7rem;">
                <i class="fas fa-shield-alt text-success me-1"></i> Transacciones monitoreadas en tiempo real por NEXUS_AUDIT.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
