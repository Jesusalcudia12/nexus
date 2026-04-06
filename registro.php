<?php
// registro.php
require 'config.php';
session_start();

// Si ya tiene sesión, mandarlo al dashboard
if(isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

// Capturar el referido automáticamente si viene de la URL (?ref=XXX)
$ref_auto = "";
if (isset($_GET['ref'])) {
    $ref_auto = htmlspecialchars($_GET['ref']);
} elseif (isset($_SESSION['ref_code'])) {
    $ref_auto = $_SESSION['ref_code'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

        :root {
            --bg-dark: #020617;
            --nexus-blue: #0ea5e9;
            --nexus-green: #10b981;
            --text-light: #f8fafc;
            --glass-white: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body { 
            background-color: var(--bg-dark); 
            color: var(--text-light); 
            font-family: 'Poppins', sans-serif;
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
            position: relative;
            overflow-y: auto;
            padding: 40px 20px;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: 
                radial-gradient(circle at 10% 10%, rgba(14, 165, 233, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(16, 185, 129, 0.1) 0%, transparent 40%);
            z-index: -1;
            filter: blur(80px);
        }

        .register-card { 
            background: rgba(15, 23, 42, 0.6); 
            border: 1px solid var(--glass-border); 
            padding: 2.5rem; 
            border-radius: 24px; 
            width: 100%; 
            max-width: 550px; 
            backdrop-filter: blur(20px); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }

        .brand-logo { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; text-align: center; }
        .brand-logo span { color: var(--nexus-green); }

        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }
        .step {
            width: 35px; height: 35px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; color: rgba(255,255,255,0.4);
        }
        .step.active { border-color: var(--nexus-blue); color: var(--nexus-blue); box-shadow: 0 0 10px rgba(14, 165, 233, 0.3); }
        .step-line { height: 2px; width: 40px; background: var(--glass-border); margin: 0 10px; }

        .form-control { 
            background: rgba(0, 0, 0, 0.4) !important; 
            border: 1px solid var(--glass-border) !important; 
            color: white !important; 
            padding: 12px; 
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .form-control:focus { 
            border-color: var(--nexus-blue) !important; 
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.2); 
        }

        .btn-nexus { 
            background: linear-gradient(135deg, var(--nexus-blue), var(--nexus-green)); 
            color: #020617; 
            font-weight: 800; 
            letter-spacing: 1px; 
            border: none; 
            padding: 14px; 
            border-radius: 12px;
            transition: 0.3s; 
            text-transform: uppercase;
        }

        .btn-nexus:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); 
        }

        .footer-links a { color: var(--nexus-blue); text-decoration: none; font-size: 0.85rem; transition: 0.3s; }
        .footer-links a:hover { color: var(--nexus-green); }
        
        .legal-text { font-size: 0.75rem; color: rgba(255, 255, 255, 0.5); line-height: 1.5; }
        
        /* Estilo personalizado para la Alerta NEXUS */
        .nexus-alert {
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 0.85rem;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="brand-logo"><i class="fas fa-satellite-dish me-2" style="color:var(--nexus-blue)"></i>NEXUS<span>.OS</span></div>
    <h4 class="text-center fw-bold mb-4">Registro</h4>

    <div class="step-indicator">
        <div class="step active"><i class="fas fa-user"></i></div>
        <div class="step-line"></div>
        <div class="step"><i class="fas fa-shield-alt"></i></div>
        <div class="step-line"></div>
        <div class="step"><i class="fas fa-check"></i></div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert nexus-alert py-2 mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php 
                switch($_GET['error']) {
                    case 'password_mismatch': echo "Las contraseñas no coinciden."; break;
                    case 'password_weak': echo "Clave débil: requiere 8 caracteres, una mayúscula y un número."; break;
                    case 'db_error': echo "Error de base de datos o correo ya registrado."; break;
                    default: echo "Error en el protocolo de registro.";
                }
            ?>
        </div>
    <?php endif; ?>

    <form action="procesar_registro.php" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small text-secondary fw-bold text-uppercase">Nombre</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small text-secondary fw-bold text-uppercase">Apellido</label>
                <input type="text" name="apellido" class="form-control" placeholder="Ej: Pérez" required>
            </div>

            <div class="col-md-12">
                <label class="form-label small text-secondary fw-bold text-uppercase">Correo electrónico</label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small text-secondary fw-bold text-uppercase">Contraseña</label>
                <input type="password" name="pass1" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small text-secondary fw-bold text-uppercase">Confirmar</label>
                <input type="password" name="pass2" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="col-md-12 mt-4">
                <div style="background: rgba(14, 165, 233, 0.05); padding: 15px; border-radius: 12px; border: 1px dashed var(--glass-border);">
                    <label class="form-label small text-info fw-bold text-uppercase">
                        <i class="fas fa-gift me-1"></i> Código de Referido (Opcional)
                    </label>
                    <input type="text" name="referido_por" class="form-control" placeholder="NX-0000" value="<?= $ref_auto ?>">
                    <div class="legal-text mt-1" style="font-size: 0.65rem;">Si un socio te invitó, ingresa su código para activar bonificaciones.</div>
                </div>
            </div>
        </div>

        <div class="form-check my-4 small">
            <input class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label text-secondary" for="terms">
                Consulte <a href="#" class="text-info text-decoration-none">los avisos legales</a> y términos de servicio.
            </label>
        </div>

        <button type="submit" class="btn btn-nexus w-100">CREAR CUENTA</button>
    </form>

    <div class="text-center mt-4 footer-links">
        <p class="small text-secondary">¿YA TIENES UNA CUENTA? <a href="login.php" class="fw-bold">INICIAR SESIÓN</a></p>
        <p class="legal-text mt-4 mb-0">© 2025 NEXU. Todos los derechos reservados.</p>
    </div>
</div>

</body>
</html>
