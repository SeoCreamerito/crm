<?php
/**
 * LIMPIAR SESIÓN COMPLETAMENTE
 * Accede a este archivo para destruir la sesión y empezar de cero
 */

session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesión Limpiada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #10b981;
            font-size: 3rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="success">✅</div>
        <h2>Sesión Limpiada Correctamente</h2>
        <p>Todas las cookies y variables de sesión han sido eliminadas.</p>
        <p>Ahora puedes iniciar sesión de nuevo sin problemas de bucle.</p>
        <a href="login.php" class="btn">Ir al Login</a>
    </div>
</body>
</html>
