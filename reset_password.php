<?php
require_once '../includes/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validar
    if (empty($password) || strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar token válido y no usado
        $stmt = $conexion->prepare("
            SELECT email FROM password_resets 
            WHERE token = ? AND usado = 0 AND creado_en > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Actualizar contraseña
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $stmt2->bind_param("ss", $hashed, $row['email']);
            $stmt2->execute();

            // Marcar token como usado
            $stmt3 = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE token = ?");
            $stmt3->bind_param("s", $token);
            $stmt3->execute();

            $success = true;
        } else {
            $error = "El enlace no es válido o ha expirado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- 🔹 AÑADIDO: CSS personalizado -->    
<link rel="stylesheet" href="/crm_llamadas/assets/css/main.css">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center min-vh-100">
    <div class="card p-4 shadow mx-auto" style="max-width: 400px;">
        <h4 class="text-center mb-4">Nueva contraseña</h4>
        <?php if ($success): ?>
            <div class="alert alert-success">
                Contraseña actualizada. Ya puedes <a href="login.php">iniciar sesión</a>.
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label>Repetir contraseña</label>
                    <input type="password" name="password2" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Restablecer</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<!-- 🔹 AÑADIDO: JS personalizado -->   
<script src="/crm_llamadas/assets/js/main.js"></script>
</body>
</html>