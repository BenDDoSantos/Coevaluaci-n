<?php
// --- LÍNEAS PARA DEPURACIÓN ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -----------------------------------------
// Iniciar sesión para manejar variables de usuario
session_start();

// Configurable session timeout in seconds (15 minutes)
$session_timeout = 900;

// Check for session inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired due to inactivity
    session_unset();
    session_destroy();
    header("Location: login.php?error=Sesión expirada por inactividad");
    exit();
}

// Update last activity time to current timestamp
$_SESSION['last_activity'] = time();

$servidor = "localhost";
$usuario_db = "root"; // Cambia por tu usuario de MySQL
$password_db = ""; // Cambia por tu contraseña de MySQL
$nombre_db = "coeval_db";

// Crear conexión
$conn = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

// Chequear conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para redirigir si el usuario no está logueado
function verificar_sesion($solo_docentes = false) {
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: index.php");
        exit();
    }
    if ($solo_docentes && (!isset($_SESSION['es_docente']) || !$_SESSION['es_docente'])) {
        header("Location: index.php");
        exit();
    }
}
?>
