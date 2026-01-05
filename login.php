<?php
/**
 * LOGIN - CRM Llamadas
 * VERSIÓN ANTI-BUCLE - Manejo correcto de sesiones
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Iniciar sesión SOLO si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, redirigir (pero SOLO una vez)
if (isset($_SESSION['id_usuario']) && isset($_SESSION['rol'])) {
    // Prevenir bucle: solo redirigir si no venimos de un redirect
    if (!isset($_GET['redirect_loop'])) {
        if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'gestion_cursos') {
            header('Location: ../admin/dashboard.php');
            exit;
        } else {
            header('Location: ../teleoperadora/dashboard.php');
            exit;
        }
    }
}

// Conexión
$host = 'localhost';
$db   = 'geae_crm_llamadas';
$user = 'geae_crm_llamadas';
$pass = '&4222SFCrb1975';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$error = '';
$debug_info = '';

// MODO DEBUG: Mostrar información de sesión (SOLO TEMPORAL)
if (isset($_GET['debug'])) {
    $debug_info = "<div class='alert alert-info'>";
    $debug_info .= "<strong>DEBUG - Variables de sesión:</strong><br>";
    if (!empty($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $debug_info .= "$key: " . htmlspecialchars($value) . "<br>";
        }
    } else {
        $debug_info .= "No hay variables de sesión";
    }
    $debug_info .= "</div>";
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email_input = trim($_POST['email']);
    $password_input = $_POST['password'];
    
    if (empty($email_input) || empty($password_input)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        try {
            // Buscar usuario por EMAIL
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = 'Usuario no encontrado o inactivo';
            } elseif (!password_verify($password_input, $user['password'])) {
                $error = 'Contraseña incorrecta';
            } else {
                // ✅ LOGIN EXITOSO
                
                // Limpiar sesión anterior
                session_regenerate_id(true);
                
                // Guardar TODAS las variables necesarias
                $_SESSION['id_usuario'] = (int)$user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellidos'] = $user['apellidos'] ?? '';
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['login_time'] = time();
                
                // Registrar acceso
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO registro_accesos (id_usuario, tipo_evento, fecha_hora) 
                        VALUES (?, 'login', NOW())
                    ");
                    $stmt->execute([$user['id']]);
                } catch (Exception $e) {
                    // Ignorar si no existe la tabla
                }
                
                // Redirigir según rol
                if ($user['rol'] === 'admin' || $user['rol'] === 'gestion_cursos') {
                    header('Location: ../admin/dashboard.php');
                    exit;
                } else {
                    header('Location: ../teleoperadora/dashboard.php');
                    exit;
                }
            }
            
        } catch (Exception $e) {
            $error = 'Error en el sistema: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM Llamadas GEAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 45px;
        }
        .logo {
            text-align: center;
            margin-bottom: 35px;
        }
        .logo i {
            font-size: 4rem;
            color: #667eea;
        }
        .logo h3 {
            margin-top: 15px;
            color: #1f2937;
            font-weight: 600;
        }
        .logo p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .input-group-text {
            background: #f9fafb;
            border-right: none;
        }
        .form-control {
            border-left: none;
            padding: 12px;
        }
        .form-label {
            font-weight: 500;
            color: #374151;
        }
        .btn-debug {
            position: fixed;
            bottom: 10px;
            right: 10px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="login-container">
    <?php echo $debug_info; ?>
    
    <div class="login-card">
        <div class="logo">
            <i class="bi bi-telephone-fill"></i>
            <h3>CRM Llamadas</h3>
            <p class="mb-0">Sistema de Gestión GEAE</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Email
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="tu@email.com"
                           required 
                           autofocus
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="••••••••"
                           required>
                </div>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Acceso seguro protegido
            </small>
        </div>
        
        <?php if (isset($_SESSION['id_usuario'])): ?>
        <div class="alert alert-warning mt-3">
            <small>⚠️ Ya tienes una sesión activa. Si ves este mensaje, hay un problema de redirección.</small>
            <br>
            <a href="../admin/dashboard.php" class="btn btn-sm btn-warning mt-2">Ir al Dashboard</a>
            <a href="?debug" class="btn btn-sm btn-secondary mt-2">Ver Debug</a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-3">
        <small class="text-white">
            © <?php echo date('Y'); ?> GEAE - CRM Llamadas
        </small>
    </div>
</div>

<!-- Botón debug oculto -->
<a href="?debug" class="btn btn-sm btn-secondary btn-debug">🔧</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
