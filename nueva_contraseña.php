<?php
session_start();
// Seguridad: Si no hay un correo validado por token, redirigir al login
if (!isset($_SESSION['email_reset'])) { 
    header("Location: login.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Restablecer Clave</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

        :root {
            --bg-dark: #020617;
            --nexus-blue: #0ea5e9;
            --nexus-green: #10b981;
            --text-light: #f8fafc;
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

        .reset-card { 
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
            padding: 12px; 
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

        .alert-weak {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <div class="brand-logo"><i class="fas fa-satellite-dish me-2" style="color:var(--nexus-blue)"></i>NEXUS<span></span></div>
    <h4 class="text-center fw-bold mb-4">Resetear Clave</h4>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-weak py-2 mb-3 text-center">
            <i class="fas fa-shield-alt me-2"></i>
            <?php 
                if($_GET['error'] == 'weak') echo "Debe tener 8+ caracteres, mayúscula y número.";
                elseif($_GET['error'] == 'mismatch') echo "Las contraseñas no coinciden.";
                else echo "Ocurrió un error. Intente de nuevo.";
            ?>
        </div>
    <?php endif; ?>

    <form action="actualizar_password.php" method="POST">
        <div class="mb-3">
            <label class="form-label small text-secondary fw-bold text-uppercase">Nueva Contraseña</label>
            <input type="password" name="pass1" class="form-control" placeholder="••••••••" required autofocus>
        </div>

        <div class="mb-4">
            <label class="form-label small text-secondary fw-bold text-uppercase">Repetir Contraseña</label>
            <input type="password" name="pass2" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-nexus w-100">ACTUALIZAR</button>
    </form>

    <div class="text-center mt-4">
        <p class="small text-secondary mb-0">© 2025 Nexu. Protocolo de Seguridad Activo.</p>
    </div>
</div>

</body>
</html>
