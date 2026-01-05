<?php
/**
 * VERIFICADOR DE LOGIN - VERSIÓN SIMPLE
 * Sin dependencias de config.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos de conexión directos
$host = 'localhost';
$db   = 'geae_crm_llamadas';
$user = 'geae_crm_llamadas';
$pass = '&4222SFCrb1975';

$conexion_ok = false;
$pdo = null;
$error_conexion = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion_ok = true;
} catch (PDOException $e) {
    $error_conexion = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificador Login - CRM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        input, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #4f46e5;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #4338ca;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f9fafb;
        }
    </style>
</head>
<body>

<h1>🔐 Verificador de Login - CRM</h1>

<div class="card">
    <h2>1. Conexión a Base de Datos</h2>
    <?php if ($conexion_ok): ?>
        <p class="success">✅ Conexión exitosa a la base de datos</p>
    <?php else: ?>
        <p class="error">❌ Error de conexión: <?php echo htmlspecialchars($error_conexion); ?></p>
    <?php endif; ?>
</div>

<?php if ($conexion_ok): ?>

<div class="card">
    <h2>2. Usuarios en el Sistema</h2>
    <?php
    try {
        $stmt = $pdo->query("SELECT id, usuario, nombre, apellidos, rol, activo FROM usuarios ORDER BY id");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($usuarios)) {
            echo "<p class='warning'>⚠️ No hay usuarios en la base de datos</p>";
        } else {
            echo "<p class='success'>✅ Total usuarios: " . count($usuarios) . "</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Activo</th></tr>";
            foreach ($usuarios as $u) {
                $activo = $u['activo'] ? '✅' : '❌';
                echo "<tr>";
                echo "<td>{$u['id']}</td>";
                echo "<td><strong>" . htmlspecialchars($u['usuario']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) . "</td>";
                echo "<td><strong>" . htmlspecialchars($u['rol']) . "</strong></td>";
                echo "<td>{$activo}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</div>

<div class="card">
    <h2>3. Probar Login</h2>
    <p>Ingresa tus credenciales para probar:</p>
    
    <form method="POST" style="max-width: 400px;">
        <label><strong>Usuario:</strong></label>
        <input type="text" name="usuario" required style="width: 100%;">
        
        <label><strong>Contraseña:</strong></label>
        <input type="password" name="password" required style="width: 100%;">
        
        <button type="submit" name="probar_login" style="width: 100%; margin-top: 10px;">
            🔐 Probar Login
        </button>
    </form>
    
    <?php
    if (isset($_POST['probar_login'])) {
        $usuario_input = trim($_POST['usuario']);
        $password_input = $_POST['password'];
        
        echo "<div style='margin-top: 20px; padding: 15px; background: #f0f9ff; border-left: 4px solid #3b82f6;'>";
        echo "<h3>📋 Resultado del Test:</h3>";
        
        try {
            // Buscar usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo "<p class='error'>❌ Usuario no encontrado</p>";
            } elseif ($user['activo'] != 1) {
                echo "<p class='error'>❌ Usuario inactivo</p>";
            } else {
                echo "<p class='success'>✅ Usuario encontrado</p>";
                echo "<p>ID: <strong>{$user['id']}</strong></p>";
                echo "<p>Nombre: <strong>" . htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']) . "</strong></p>";
                echo "<p>Rol: <strong>" . htmlspecialchars($user['rol']) . "</strong></p>";
                
                // Verificar contraseña
                if (password_verify($password_input, $user['password'])) {
                    echo "<p class='success'>✅ Contraseña CORRECTA</p>";
                    
                    // Crear sesión
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['nombre'] = $user['nombre'];
                    $_SESSION['apellidos'] = $user['apellidos'];
                    $_SESSION['rol'] = $user['rol'];
                    
                    echo "<div style='background: #f0fdf4; padding: 15px; margin-top: 15px; border-radius: 5px;'>";
                    echo "<p class='success'><strong>✅ SESIÓN CREADA EXITOSAMENTE</strong></p>";
                    echo "<p><strong>Variables guardadas:</strong></p>";
                    echo "<ul>";
                    echo "<li>id_usuario: <code>{$_SESSION['id_usuario']}</code></li>";
                    echo "<li>usuario: <code>{$_SESSION['usuario']}</code></li>";
                    echo "<li>nombre: <code>{$_SESSION['nombre']}</code></li>";
                    echo "<li>apellidos: <code>{$_SESSION['apellidos']}</code></li>";
                    echo "<li>rol: <code>{$_SESSION['rol']}</code></li>";
                    echo "</ul>";
                    echo "</div>";
                    
                    echo "<div style='margin-top: 20px;'>";
                    echo "<a href='../admin/verificar_sesion.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>✅ Verificar Sesión</a>";
                    echo "<a href='../admin/dashboard.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>🏠 Ir al Dashboard</a>";
                    echo "<a href='../admin/gestion_cursos.php' style='background: #8b5cf6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>📚 Gestión Cursos</a>";
                    echo "</div>";
                    
                } else {
                    echo "<p class='error'>❌ Contraseña INCORRECTA</p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "</div>";
    }
    ?>
</div>

<div class="card" style="background: #fffbeb; border: 2px solid #f59e0b;">
    <h2>⚠️ Problema Identificado</h2>
    <p><strong>Tu login.php NO está guardando correctamente las variables de sesión.</strong></p>
    
    <h3>Lo que debe hacer login.php:</h3>
    <pre style="background: #f9fafb; padding: 15px; border-radius: 5px; overflow-x: auto;">
// DESPUÉS de verificar usuario y contraseña:
session_start();

// GUARDAR ESTAS 5 VARIABLES (CRÍTICO):
$_SESSION['id_usuario'] = $user['id'];
$_SESSION['usuario'] = $user['usuario'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['apellidos'] = $user['apellidos'];
$_SESSION['rol'] = $user['rol'];

// Redirigir
header('Location: ../admin/dashboard.php');
exit;</pre>

    <p><strong>Si el login funciona AQUÍ pero no en login.php:</strong></p>
    <ol>
        <li>El archivo <code>login.php</code> tiene un error</li>
        <li>Necesitas reemplazarlo con una versión corregida</li>
    </ol>
</div>

<?php endif; ?>

</body>
</html>
