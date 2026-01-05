<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (isset($_SESSION['sesion_actividad_id'])) {
    $stmt = $pdo->prepare("UPDATE sesiones_actividad SET fecha_salida = NOW() WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['sesion_actividad_id']]);
}

session_destroy();
header('Location: login.php');
exit;
?>
