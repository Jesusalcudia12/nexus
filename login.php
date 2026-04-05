<?php
// login.php
require '../includes/config.php'; 
session_start();
if(isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = "";
if (isset($_GET['error'])) {
    $error = "Credenciales incorrectas o acceso denegado.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Acceso de Seguridad</title>
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

        /* FONDO DEGRADADO RADIAL */
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

        .login-card { 
            background: rgba(15, 23, 42, 0.6); 
            border: 1px solid var(--glass-border); 
            padding: 2.5rem; 
            border-radius: 24px; 
            width: 100%; 
            max-width: 450px; 
            backdrop-filter: blur(20px); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }

        .brand-logo { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .brand-logo span { color: var(--nexus-green); }

        .form-control { 
            background: rgba(0, 0, 0, 0.4) !important; 
            border: 1px solid var(--glass-border) !important; 
            color: white !important; 
            padding: 14px; 
            border-radius: 12px;
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
        .footer-links a:hover { color: var(--nexus-green); text-decoration: underline; }
        
        .legal-text { font-size: 0.75rem; color: rgba(255, 255, 255, 0.5); line-height: 1.5; }
        .risk-notice { font-size: 0.7rem; color: rgba(255, 255, 255, 0.3); border-top: 1px solid var(--glass-border); padding-top: 15px; margin-top: 20px; text-align: justify; }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="brand-logo"><i class="fas fa-satellite-dish me-2" style="color:var(--nexus-blue)"></i>NEXUS<span>.OS</span></div>
    <p class="text-secondary small mb-4">Inicia sesión para acceder a tu nodo</p>

    <?php if($error): ?>
        <div class="alert alert-danger py-2 small"><?= $error ?></div>
    <?php endif; ?>

    <form action="auth/procesar_login.php" method="POST" class="mt-4 text-start">
        <div class="mb-3">
            <label class="form-label small text-uppercase fw-bold" style="letter-spacing: 1px; color: rgba(255,255,255,0.7);">Identificación de Usuario</label>
            <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="mb-4">
            <label class="form-label small text-uppercase fw-bold" style="letter-spacing: 1px; color: rgba(255,255,255,0.7);">Clave de Acceso</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-nexus w-100">INICIAR SESIÓN</button>
    </form>

    <div class="footer-links mt-4">
        <div class="mb-2">
            <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
        </div>
        <div class="mb-2">
            <a href="registro.php">¿Necesitas crear una cuenta? <span class="fw-bold">¡Regístrate!</span></a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="text-secondary small"><i class="fas fa-arrow-left me-1"></i> Volver al sitio web oficial</a>
        </div>
        
        <p class="legal-text mt-4 mb-0">© 2025 Nixo. Todos los derechos reservados.</p>
    </div>

    <div class="risk-notice">
        <strong>Aviso sobre riesgos de inversión:</strong> Invertir conlleva riesgos, incluyendo la posible pérdida del capital invertido. El rendimiento pasado no garantiza resultados futuros. Consulte con un asesor financiero.
    </div>
</div>

</body>
</html>
