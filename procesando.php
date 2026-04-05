<body style="background:#f1f5f9; font-family:sans-serif; display:flex; align-items:center; justify-content:center; height:100vh;">
    <div style="text-align:center; background:white; padding:50px; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.05);">
        <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #10b981; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
        <h3 style="color:#0f172a;">Validando con su institución...</h3>
        <p style="color:#64748b; font-size:14px;">Por favor, no cierre esta ventana. <br> Estamos autorizando el monto de $<?php echo number_format($_GET['m'], 2); ?> con su banco.</p>
    </div>
    <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    <script>
        // Después de 10 segundos, lo mandamos al dashboard con el saldo "pendiente"
        setTimeout(function(){
            window.location.href = "dashboard.php?status=pending";
        }, 10000);
    </script>
</body>
