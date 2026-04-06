<?php
// Solo mantenemos la detección de referido para no perder el rastro del socio
require 'config.php';
session_start();

if (isset($_GET['ref'])) {
    $_SESSION['ref_code'] = mysqli_real_escape_string($conn, $_GET['ref']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Tu Conexión al Éxito Financiero</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

        :root {
            --bg-dark: #020617;
            --nexus-blue: #0ea5e9;
            --nexus-green: #10b981;
            --text-light: #f8fafc;
            --glass-white: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.05);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            overflow-x: hidden;
            position: relative;
            scroll-behavior: smooth;
        }

        /* FONDO ESTILO NIXO */
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

        .navbar {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            padding: 15px 0;
        }

        .navbar-brand { font-size: 1.8rem; font-weight: 800; color: #fff !important; }
        .navbar-brand span { color: var(--nexus-green); }

        .btn-access {
            background: var(--glass-white);
            color: #fff !important;
            border: 1px solid var(--glass-border);
            padding: 8px 25px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-right: 15px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-start {
            background: linear-gradient(135deg, var(--nexus-blue), var(--nexus-green));
            color: var(--bg-dark) !important;
            padding: 10px 30px;
            border-radius: 50px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: 0.3s;
        }

        .hero { padding: 180px 0 120px; text-align: center; }
        .hero-title { font-size: 4.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 25px; }
        .hero-title span {
            background: linear-gradient(90deg, #fff, var(--nexus-blue), var(--nexus-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card {
            background: var(--glass-white);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 40px;
            transition: 0.3s;
        }

        .feature-card:hover { transform: translateY(-10px); border-color: var(--nexus-blue); }
        
        .accordion-item { background: transparent !important; border: 1px solid var(--glass-border) !important; margin-bottom: 10px; }
        .accordion-button { background: var(--glass-white) !important; color: white !important; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-satellite-dish me-2" style="color:var(--nexus-blue)"></i>NEXUS<span></span></a>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a href="#" class="nav-link text-white">Hogar</a></li>
                    <li class="nav-item"><a href="#acerca" class="nav-link text-white">Acerca de</a></li>
                    <li class="nav-item"><a href="#terminos" class="nav-link text-white">Términos</a></li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="login.php" class="btn-access text-decoration-none">Login</a>
                    <a href="registro.php" class="btn-start text-decoration-none">Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Nexus.<br>Conecta con tu<br><span>Riqueza Real</span></h1>
            <p class="hero-subtitle text-secondary fs-5 mb-5">El ecosistema financiero exclusivo para desbloquear tu potencial.<br>No simulamos, operamos bajo protocolos de élite.</p>
            <div class="d-flex justify-content-center">
                <a href="registro.php" class="btn-start btn-lg me-3 text-decoration-none">EMPEZAR AHORA</a>
                <a href="login.php" class="btn-access btn-lg text-decoration-none" style="padding: 12px 40px;">LOGIN</a>
            </div>
        </div>
    </section>

    <section id="acerca" class="py-5" style="background: rgba(0,0,0,0.2);">
        <div class="container py-5">
            <h2 class="text-center mb-5 fw-bold">La Diferencia <span>NEXUS</span></h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-network-wired mb-3 fs-1 text-info"></i>
                        <h3>Conexión Global</h3>
                        <p class="text-secondary">Únete a una red cerrada de inversores y accede a oportunidades reales de crecimiento.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt mb-3 fs-1 text-info"></i>
                        <h3>Seguridad Blindada</h3>
                        <p class="text-secondary">Protección  AES-256 para cada uno de tus activos y datos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-bolt mb-3 fs-1 text-info"></i>
                        <h3>Resultados Reales</h3>
                        <p class="text-secondary">Cada movimiento impacta directamente en tu balance operativo.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="terminos" class="py-5">
        <div class="container py-5">
            <h2 class="text-center mb-5 fw-bold">Protocolos de <span>Operación</span></h2>
            <div class="accordion mx-auto" id="accTerms" style="max-width: 800px;">
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#t1">01. Operativa Sin Simulación</button></h2>
                    <div id="t1" class="accordion-collapse collapse" data-bs-parent="#accTerms">
                        <div class="accordion-body text-secondary">NEXUS es una plataforma de gestión real. El usuario asume la responsabilidad de las operaciones ejecutadas en su nodo.</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#t2">02. Comisiones de Red</button></h2>
                    <div id="t2" class="accordion-collapse collapse" data-bs-parent="#accTerms">
                        <div class="accordion-body text-secondary">Para garantizar la estabilidad del ecosistema, se aplica una comisión fija del 5% en cada retiro de activos.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-5 text-center border-top border-secondary mt-5">
        <p class="mb-0">© 2026 NEXUS - Conexión de Seguridad Blindada.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
