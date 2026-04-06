<?php
// recuperar_contraseña.php
require 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Restablecer Contraseña</title>
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
            overflow: hidden;
            padding: 20px;
        }

        /* FONDO DEGRADADO RADIAL (Identidad Nexus) */
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

        .recovery-card { 
            background: rgba(15, 23, 42, 0.6); 
            border: 1px solid var(--glass-border); 
            padding: 2.5rem; 
            border-radius: 24px; 
            width: 100%; 
            max-width: 450px; 
            backdrop-filter: blur(20px); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }

        .brand-logo { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; text-align: center; }
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
        .alert-nexus {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            border-radius: 12px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="recovery-card text-center">
    <div class="brand-logo"><i class="fas fa-satellite-dish me-2" style="color:var(--nexus-blue)"></i>NEXUS<span></span></div>
    <h4 class="fw-bold mb-3">Restablecer contraseña</h4>
    <p class="text-secondary small mb-4">Introduce tu correo electrónico registrado para recibir un enlace de restablecimiento.</p>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'sent'): ?>
        <div class="alert alert-nexus py-2 mb-4">
            <i class="fas fa-paper-plane me-2"></i> Enlace enviado. Revisa tu bandeja de entrada.
        </div>
    <?php endif; ?>

    <form action="procesar_recuperacion.php" method="POST" class="text-start">
        <div class="mb-4">
            <label class="form-label small text-uppercase fw-bold" style="letter-spacing: 1px; color: rgba(255,255,255,0.7);">Correo electrónico</label>
            <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
        </div>
        
        <button type="submit" class="btn btn-nexus w-100">ENVIAR ENLACE</button>
    </form>

    <div class="footer-links mt-4">
        <div class="mb-2">
            <a href="login.php">Volver a Iniciar sesión</a>
        </div>
        <div class="mb-2">
            <a href="index.php" class="text-secondary small">Volver al sitio web oficial</a>
        </div>
        
        <p class="legal-text mt-4 mb-0">© 2025 Nixo. Todos los derechos reservados.</p>
        <p class="legal-text" style="font-size: 0.65rem; opacity: 0.5;">
            Aviso sobre riesgos de inversión: Invertir conlleva riesgos, incluyendo la posible pérdida del capital invertido.
        </p>
    </div>
</div>

</body>
</html>
