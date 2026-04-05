<?php
// verificar_token.php
$email = $_GET['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>NEXUS - Verificar Código</title>
    </head>
<body style="background: #0a0a0a; color: white;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4 card p-4 bg-dark border-info">
                <?php include 'brand.php'; ?>
                <h5 class="text-center mt-3">VERIFICAR CÓDIGO</h5>
                <form action="auth/confirmar_token.php" method="POST">
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <div class="mb-3">
                        <label class="small">INGRESA EL CÓDIGO DE 6 DÍGITOS</label>
                        <input type="text" name="token" class="form-control text-center" maxlength="6" placeholder="000000" required>
                    </div>
                    <button type="submit" class="btn btn-info w-100">VERIFICAR</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
