<?php
// public/perfil.php
require '../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// --- LÓGICA DE IMAGEN DESDE BASE DE DATOS (BLOB A BASE64) ---
$foto_src = "img/default.png"; // Imagen por defecto si la DB está vacía
if (!empty($user['foto_perfil'])) {
    $base64 = base64_encode($user['foto_perfil']);
    $foto_src = 'data:image/jpeg;base64,' . $base64;
}

// Generar Link de Referido dinámico
$link_referido = "https://tu-dominio.com/public/registro.php?ref=" . $user['mi_codigo_referido'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS.OS | Expediente de Operador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        :root {
            --bg-dark: #020617;
            --nexus-blue: #0ea5e9;
            --nexus-purple: #8b5cf6;
            --glass: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body { 
            background-color: var(--bg-dark); 
            color: #f8fafc; 
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        /* BARRA LATERAL */
        .sidebar { 
            width: 250px; height: 100vh; background: rgba(15, 23, 42, 0.95); 
            position: fixed; border-right: 1px solid var(--glass-border); 
            backdrop-filter: blur(15px); z-index: 1000;
        }

        .nav-link { 
            color: rgba(255,255,255,0.4); padding: 12px 25px; border-radius: 12px; 
            margin: 4px 15px; transition: 0.3s; font-size: 0.8rem; text-decoration: none; display: block;
        }

        .nav-link:hover, .nav-link.active { background: rgba(14, 165, 233, 0.08); color: var(--nexus-blue); }

        /* CONTENIDO PRINCIPAL */
        .content { margin-left: 250px; padding: 40px; }

        .profile-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(15px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }

        /* AVATAR */
        .avatar-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 25px;
        }

        .avatar-image {
            width: 100%; height: 100%; border-radius: 50%;
            object-fit: cover; border: 3px solid var(--nexus-purple);
            padding: 4px; background: var(--bg-dark);
        }

        .btn-edit-photo {
            position: absolute; bottom: 5px; right: 5px;
            background: var(--nexus-purple); color: white;
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 3px solid var(--bg-dark); transition: 0.3s;
        }

        .btn-edit-photo:hover { transform: scale(1.1); background: #d946ef; }

        /* BLOQUES DE DATOS */
        .data-box {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--glass-border);
            padding: 18px 22px;
            border-radius: 16px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .data-box:hover { border-color: var(--nexus-purple); }

        .label-text {
            font-size: 0.65rem;
            color: var(--nexus-blue);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-bottom: 5px;
        }

        .value-text { font-size: 1rem; color: #f1f5f9; font-weight: 400; }

        /* LINK DE REFERIDO */
        .referral-link-box {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.1), transparent);
            border-left: 4px solid var(--nexus-purple);
        }

        .copy-btn {
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid var(--nexus-purple);
            color: white; padding: 5px 12px;
            border-radius: 8px; font-size: 0.75rem;
            cursor: pointer; transition: 0.3s;
        }

        .copy-btn:hover { background: var(--nexus-purple); }

        .btn-update {
            background: linear-gradient(135deg, var(--nexus-blue), var(--nexus-purple));
            border: none; color: white; font-weight: 600; padding: 14px;
            border-radius: 14px; width: 100%; margin-top: 15px; transition: 0.3s;
        }
        .btn-update:hover { filter: brightness(1.2); transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="pt-4 px-4 mb-4 text-center">
        <h4 class="fw-bold mb-0">NEXUS<span style="color: var(--nexus-purple);">.OS</span></h4>
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link" href="../includes/dashboard.php"><i class="fa-solid fa-house-user me-2"></i> DASHBOARD</a>
        <a class="nav-link active" href="perfil.php"><i class="fa-solid fa-user-gear me-2"></i> PERFIL</a>
        <a class="nav-link" href="depositar.php"><i class="fa-solid fa-bolt me-2"></i> INVERTIR</a>
        <a class="nav-link" href="referidos.php"><i class="fa-solid fa-share-nodes me-2"></i> MI RED</a>
    </nav>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="profile-card">
                
                <form action="../includes/update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center">
                        <div class="avatar-container">
                            <img src="<?php echo $foto_src; ?>" class="avatar-image" id="img-preview">
                            <label for="foto_input" class="btn-edit-photo">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" name="foto_perfil" id="foto_input" style="display: none;" onchange="previewImage(event)">
                        </div>
                        <h3 class="fw-bold mb-0 text-white"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h3>
                        <p class="text-secondary small mb-5">Operador Verificado • #<?php echo $user['mi_codigo_referido']; ?></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-box">
                                <span class="label-text">Nombre Completo</span>
                                <span class="value-text"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-box">
                                <span class="label-text">RFC / Tax ID</span>
                                <span class="value-text"><?php echo $user['rfc'] ?? 'S/N'; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-box">
                                <span class="label-text">Número Móvil</span>
                                <span class="value-text"><?php echo $user['telefono'] ?? 'No vinculado'; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-box">
                                <span class="label-text">Correo Electrónico</span>
                                <span class="value-text"><?php echo $user['email']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-box">
                                <span class="label-text">Código de Referido</span>
                                <span class="value-text text-info fw-bold"><?php echo $user['mi_codigo_referido']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="data-box referral-link-box d-flex justify-content-between align-items-center">
                                <div style="overflow: hidden;">
                                    <span class="label-text">Mi Enlace Personalizado</span>
                                    <span class="value-text small text-secondary" id="linkText"><?php echo $link_referido; ?></span>
                                </div>
                                <button type="button" class="copy-btn" onclick="copyLink()">
                                    <i class="fa-solid fa-copy me-1"></i> COPIAR
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-update">ACTUALIZAR DATOS DE EXPEDIENTE</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    // Vista previa instantánea de la imagen
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('img-preview');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    // Función para copiar el enlace
    function copyLink() {
        var link = document.getElementById('linkText').innerText;
        navigator.clipboard.writeText(link).then(() => {
            alert("¡Enlace de referido copiado al portapapeles!");
        });
    }
</script>

</body>
</html>
