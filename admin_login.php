<?php
// Ajuste de ruta: Subimos un nivel para entrar a includes
require '../includes/config.php';
session_start();

class OwenAuth {
    public function verify($secret, $code) {
        $slot = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if ($this->calculate($secret, $slot + $i) == $code) return true;
        }
        return false;
    }
    protected function calculate($secret, $slot) {
        $key = $this->decode32($secret);
        $time = pack('N*', 0) . pack('N*', $slot);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $val = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        return str_pad($val % 1000000, 6, '0', STR_PAD_LEFT);
    }
    protected function decode32($s) {
        $b = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        $f = array_flip(str_split($b));
        $o = ""; $v = 0; $bits = 0;
        foreach (str_split($s) as $c) {
            $v = ($v << 5) | $f[$c]; $bits += 5;
            if ($bits >= 8) { $o .= chr(($v >> ($bits - 8)) & 0xFF); $bits -= 8; }
        }
        return $o;
    }
}

$auth = new OwenAuth();
$check = $conn->query("SELECT * FROM admin_root LIMIT 1");
$exists = ($check->num_rows > 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$exists) {
        $user = $conn->real_escape_string($_POST['user']);
        $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
        $secret = strtoupper(substr(bin2hex(random_bytes(10)), 0, 16));
        $conn->query("INSERT INTO admin_root (username, password, google_secret) VALUES ('$user', '$pass', '$secret')");
        $_SESSION['temp_secret'] = $secret;
        header("Location: admin_login.php?setup=1"); exit;
    } else {
        $admin = $check->fetch_assoc();
        if (password_verify($_POST['pass'], $admin['password']) && $auth->verify($admin['google_secret'], $_POST['otp'])) {
            $_SESSION['admin_auth'] = true;
            header("Location: admin_vault.php"); exit;
        } else { $error = "SISTEMA_OWEN: ACCESO_DENEGADO. Intento fallido."; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OWEN | LOGIN_ROOT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-red: #e11d48; /* Rojo neón */
            --dark-red: #4c0519;
            --bg-black: #020617;
        }
        body { 
            background: var(--bg-black); 
            color: var(--primary-red); 
            font-family: 'Consolas', 'Courier New', monospace; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background: #000; 
            border: 2px solid var(--primary-red); 
            padding: 40px; 
            border-radius: 2px; 
            width: 400px; 
            box-shadow: 0 0 25px rgba(225, 29, 72, 0.3); 
            text-align: center;
            position: relative;
        }
        .hacking-header {
            margin-bottom: 30px;
        }
        .hacking-header i {
            font-size: 60px;
            text-shadow: 0 0 15px var(--primary-red);
            margin-bottom: 15px;
        }
        h2 { 
            font-size: 1.5rem; 
            font-weight: 900; 
            letter-spacing: 10px; 
            margin: 0;
            text-transform: uppercase;
        }
        input { 
            background: #0a0a0a !important; 
            border: 1px solid var(--dark-red) !important; 
            color: #fff !important; 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 20px; 
            text-align: center; 
            border-radius: 0; 
            outline: none;
            font-size: 0.9rem;
        }
        input:focus {
            border-color: var(--primary-red) !important;
            box-shadow: 0 0 10px rgba(225, 29, 72, 0.4);
        }
        button { 
            background: var(--primary-red); 
            color: #000; 
            width: 100%; 
            padding: 15px; 
            font-weight: 900; 
            border: none; 
            letter-spacing: 4px; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }
        button:hover { 
            background: #fff; 
            box-shadow: 0 0 20px #fff;
        }
        .qr-frame { background: white; padding: 10px; margin-bottom: 20px; display: inline-block; }
        .error-log {
            background: rgba(225, 29, 72, 0.1);
            border-left: 4px solid var(--primary-red);
            color: #fff;
            padding: 10px;
            font-size: 12px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="hacking-header">
            <i class="fas fa-mask"></i>
            <h2>OWEN</h2>
            <div style="font-size: 10px; color: #444; margin-top: 5px;">PROTOCOL_VERSION: 1.0.4</div>
        </div>

        <?php if(isset($_GET['setup'])): ?>
            <div class="qr-frame">
                <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=otpauth://totp/OWEN_SYSTEMS?secret=<?php echo $_SESSION['temp_secret']; ?>">
            </div>
            <p class="small text-white mb-4">Sincroniza tu token 2FA.<br>Secret: <span style="color: var(--primary-red);"><?php echo $_SESSION['temp_secret']; ?></span></p>
            <a href="admin_login.php" class="btn btn-outline-light btn-sm w-100" style="border-radius:0;">REINTENTAR_ACCESO</a>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="user" placeholder="ADMIN_USER" required autocomplete="off">
                <input type="password" name="pass" placeholder="PASSWORD_SECRET" required>
                <?php if($exists): ?>
                    <input type="text" name="otp" placeholder="2FA_TOKEN" maxlength="6" autocomplete="off" required>
                <?php endif; ?>
                <button type="submit">BYPASS_DOOR</button>
            </form>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="error-log">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
