<?php
// Salimos de public/ para entrar a includes/
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consultar si el usuario realmente ya llegó a la meta para permitirle estar aquí
$stmt = $conn->prepare("SELECT saldo_congelado, meta_objetivo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$saldo = $res['saldo_congelado'] ?? 0;
$meta = ($res['meta_objetivo'] > 0) ? $res['meta_objetivo'] : 1;

// BLOQUEO DE SEGURIDAD: Si no ha llegado al 100%, no puede ver esta página
if ($saldo < $meta) {
    header("Location: dashboard.php?error=meta_no_alcanzada");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification | NEXUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #020617; 
            --nexus-green: #10b981; 
            --nexus-blue: #3b82f6;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        body { 
            background-color: var(--bg); 
            color: #f8fafc; 
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% -20%, rgba(16, 185, 129, 0.1), transparent 50%);
        }
        .kyc-container { max-width: 650px; margin: 40px auto; }
        .card-nexus { 
            background: var(--glass); 
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 24px; 
            padding: 30px;
        }
        .step-icon { 
            width: 35px; height: 35px; background: rgba(255,255,255,0.05); 
            border-radius: 10px; display: flex; align-items:center; 
            justify-content: center; margin-right: 15px; font-weight: 800; 
            color: var(--nexus-green); border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .file-input-wrapper { 
            border: 2px dashed var(--glass-border); 
            border-radius: 16px; padding: 25px; text-align: center; 
            cursor: pointer; transition: 0.3s; position: relative; 
            background: rgba(0,0,0,0.2);
        }
        .file-input-wrapper:hover { border-color: var(--nexus-green); background: rgba(16, 185, 129, 0.03); }
        .file-input-wrapper input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body>

<div class="container kyc-container">
    <div class="text-center mb-5">
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 mb-3">
            <i class="fas fa-check-circle me-1"></i> META ALCANZADA
        </span>
        <h2 class="fw-800 text-white">Verificación de Protocolo</h2>
        <p class="text-muted small mono">KYC_LEVEL_01 // SECURE_WITHDRAWAL</p>
    </div>

    <div class="card-nexus shadow-lg">
        <form action="upload_kyc.php" method="POST" enctype="multipart/form-data">
            
            <div class="d-flex align-items-center mb-4">
                <div class="step-icon">1</div>
                <div>
                    <h6 class="mb-0 fw-bold text-white">Identificación Oficial</h6>
                    <p class="text-muted mb-0 small">Sube tu INE/IFE o Pasaporte vigente.</p>
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-6">
                    <div class="file-input-wrapper">
                        <i class="fas fa-id-card fa-2xl text-muted mb-3 d-block"></i>
                        <p class="small mb-0 text-secondary mono" id="txt-frente">ANVERSO_FILE</p>
                        <input type="file" name="ine_frente" accept="image/*" required onchange="updateLabel(this, 'txt-frente')">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="file-input-wrapper">
                        <i class="fas fa-address-card fa-2xl text-muted mb-3 d-block"></i>
                        <p class="small mb-0 text-secondary mono" id="txt-atras">REVERSO_FILE</p>
                        <input type="file" name="ine_atras" accept="image/*" required onchange="updateLabel(this, 'txt-atras')">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-4">
                <div class="step-icon">2</div>
                <div>
                    <h6 class="mb-0 fw-bold text-white">Prueba de Vida Biométrica</h6>
                    <p class="text-muted mb-0 small">Video corto para confirmar identidad.</p>
                </div>
            </div>

            <div class="mb-5">
                <div class="file-input-wrapper py-4">
                    <i class="fas fa-video fa-2xl text-muted mb-3 d-block"></i>
                    <p class="small mb-0 text-secondary mono" id="txt-video">UPLOAD_BIOMETRIC_VIDEO</p>
                    <input type="file" name="video_facial" accept="video/*" required onchange="updateLabel(this, 'txt-video')">
                </div>
                <div class="alert bg-info bg-opacity-5 border-info border-opacity-10 mt-3 py-3 text-info" style="font-size: 0.75rem;">
                    <i class="fas fa-info-circle me-2"></i> 
                    <strong>INSTRUCCIÓN:</strong> Graba tu rostro de frente diciendo: 
                    <em>"Mi nombre es [Nombre completo] y autorizo el retiro en NEXUS."</em>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fw-800 shadow-sm mono" style="border-radius: 14px; background: var(--nexus-green); color: #000; border: none;">
                FINALIZAR_Y_DESBLOQUEAR_SALDO
            </button>
            
            <div class="text-center mt-4">
                <a href="dashboard.php" class="text-decoration-none text-muted small mono">CANCEL_OPERATION</a>
            </div>
        </form>
    </div>

    <div class="text-center mt-5 opacity-50">
        <p class="small text-muted"><i class="fas fa-shield-halved me-1"></i> AES-256 BIT ENCRYPTION // NO THIRD-PARTY ACCESS</p>
    </div>
</div>

<script>
function updateLabel(input, id) {
    if (input.files && input.files[0]) {
        document.getElementById(id).innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle"></i> LOADED</span>`;
        document.getElementById(id).closest('.file-input-wrapper').style.borderColor = '#10b981';
        document.getElementById(id).closest('.file-input-wrapper').style.background = 'rgba(16, 185, 129, 0.05)';
    }
}
</script>

</body>
</html>
