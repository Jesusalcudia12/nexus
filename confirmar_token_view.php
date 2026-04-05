<?php
// public/confirmar_token_view.php
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Verificar Identidad</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');
        :root { --bg-dark: #020617; --nexus-blue: #0ea5e9; --nexus-green: #10b981; --glass-border: rgba(255, 255, 255, 0.1); }

        body { 
            background-color: var(--bg-dark); color: white; font-family: 'Poppins', sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0;
        }

        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 10% 10%, rgba(14, 165, 233, 0.15) 0%, transparent 40%);
            z-index: -1; filter: blur(80px);
        }

        .token-card { 
            background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); 
            padding: 2.5rem; border-radius: 24px; width: 100%; max-width: 450px; 
            backdrop-filter: blur(20px); text-align: center;
        }

        .token-input {
            background: rgba(0, 0, 0, 0.4) !important; border: 2px solid var(--glass-border) !important;
            color: var(--nexus-blue) !important; font-size: 2rem; font-weight: 800;
            letter-spacing: 10px; text-align: center; border-radius: 15px; margin-bottom: 20px;
        }

        .btn-nexus { 
            background: linear-gradient(135deg, var(--nexus-blue), var(--nexus-green)); 
            color: #020617; font-weight: 800; border: none; padding: 14px; border-radius: 12px; width: 100%;
        }
    </style>
</head>
<body>

<div class="token-card">
    <div class="mb-4 text-center">
        <i class="fas fa-user-shield fa-3x mb-3" style="color: var(--nexus-blue);"></i>
        <h4 class="fw-bold">Verificar Nodo</h4>
        <p class="text-secondary small">Hemos enviado un código de 6 dígitos a:<br><b class="text-light"><?php echo $email; ?></b></p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger py-2 small bg-transparent border-danger text-danger mb-3">
            Código inválido o expirado.
        </div>
    <?php endif; ?>

    <form action="auth/confirmar_token.php" method="POST">
        <input type="hidden" name="email" value="<?php echo $email; ?>">
        <div class="mb-3">
            <label class="form-label small text-uppercase fw-bold opacity-50">Código de Acceso</label>
            <input type="text" name="token" class="form-control token-input" placeholder="000000" maxlength="6" required autofocus>
        </div>
        <button type="submit" class="btn btn-nexus">VALIDAR CÓDIGO</button>
    </form>

    <div class="mt-4">
        <a href="recuperar_contraseña.php" class="text-secondary text-decoration-none small">¿No recibiste el código? Reintentar</a>
    </div>
</div>

</body>
</html>
