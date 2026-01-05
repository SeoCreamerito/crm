<?php
require_once '../includes/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor, introduce un email válido.";
    } else {
        // Verificar si el email existe (pero no revelarlo)
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generar token único
            $token = bin2hex(random_bytes(32));
            
            // Guardar en base de datos
            $stmt2 = $conexion->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
            $stmt2->bind_param("ss", $email, $token);
            $stmt2->execute();

            // ⚠️ Aquí iría el envío real de correo (más abajo explicamos SMTP)
            // Por ahora, mostramos el enlace en pantalla (solo para pruebas)
            $reset_link = "http://geae.es/crm_llamadas/auth/reset_password.php?token=" . urlencode($token);
            
            // ¡EN PRODUCCIÓN, COMENTA ESTA LÍNEA Y ACTIVA EL ENVÍO POR EMAIL!
            $message = "ENLACE DE PRUEBA (solo para desarrollo):<br><a href='$reset_link'>$reset_link</a>";
            
            // En producción, descomenta el envío de email (ver más abajo)
        sendPasswordResetEmail($email, $reset_link);
        }
        
        // Mensaje genérico para evitar enumeración
        $message = "Si el email está registrado, recibirás un enlace para restablecer tu contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¿Olvidaste tu contraseña?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- 🔹 AÑADIDO: CSS personalizado -->    
<link rel="stylesheet" href="/crm_llamadas/assets/css/main.css">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center min-vh-100">
    <div class="card p-4 shadow mx-auto" style="max-width: 400px;">
        <h4 class="text-center mb-4">¿Olvidaste tu contraseña?</h4>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Email registrado</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
            <div class="text-center mt-3">
                <a href="login.php">&larr; Volver al login</a>
            </div>
        </form>
    </div>
</div>
<!-- 🔹 AÑADIDO: JS personalizado -->    
<script src="/crm_llamadas/assets/js/main.js"></script>
</body>
</html>