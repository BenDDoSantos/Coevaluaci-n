<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Aseguramos que la sesión se inicie
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🚨 LÍNEAS DE DEBUGGING (Mantenidas para forzar la visualización de errores, 
// quítalas cuando vayas a producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para construir la URL absoluta y segura para la redirección
function build_absolute_url($target_file, $params = []) {
    // Determina el protocolo (http o https) y el host (localhost)
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    // Determina la carpeta raíz del proyecto (ej: /coevaluaci-n/)
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    
    // Construir la URL completa
    $url = $base_url . $path . "/" . $target_file;
    if (!empty($params)) {
        $url .= "?" . http_build_query($params);
    }
    return $url;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Asignación de variables estable y compatible
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // ------------------------------------------------
    // 1. VALIDACIÓN DE CONEXIÓN Y DE CAMPOS
    // ------------------------------------------------
    if ($conn->connect_error) {
        $url = build_absolute_url("registro.php", ["error" => urlencode("Error de servidor: Conexión DB fallida.")]);
        header("Location: " . $url);
        exit();
    }
    
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        $url = build_absolute_url("registro.php", ["error" => urlencode("Todos los campos son obligatorios.")]);
        header("Location: " . $url);
        exit();
    }

    if ($password !== $confirm_password) {
        $url = build_absolute_url("registro.php", ["error" => urlencode("Las contraseñas no coinciden.")]);
        header("Location: " . $url);
        exit();
    }

    if (strlen($password) < 6) {
        $url = build_absolute_url("registro.php", ["error" => urlencode("La contraseña debe tener al menos 6 caracteres.")]);
        header("Location: " . $url);
        exit();
    }

    // --- 2. VALIDACIÓN DE DOMINIO (Docente: @uct.cl) ---
    $dominio_requerido = "@uct.cl";
    if (!str_ends_with($email, $dominio_requerido)) {
        $url = build_absolute_url("registro.php", ["error" => urlencode("Solo se permite el registro de docentes con correos @uct.cl.")]);
        header("Location: " . $url);
        exit();
    }

    // --- 3. PROCESO DE REGISTRO con Transacción ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $conn->begin_transaction(); // Iniciar transacción
    
    try {
        // es_docente = 1 (TRUE) por defecto
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, es_docente, password) VALUES (?, ?, TRUE, ?)");
        
        if (!$stmt) {
             throw new Exception("Error al preparar la consulta SQL: " . $conn->error);
        }

        $stmt->bind_param("sss", $nombre, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $conn->commit(); // Éxito: confirmar los cambios
            
            // 🚨 ÉXITO: Redirección Absoluta a index.php
            $url = build_absolute_url("index.php", ["status" => urlencode("Registro exitoso. ¡Ahora puedes iniciar sesión!")]);
            header("Location: " . $url);
            exit();
        } else {
            $conn->rollback(); // Fallo: deshacer los cambios
            
            $error_message = "Error al registrar el usuario: " . $stmt->error;
            
            // Manejar error de correo duplicado (UNIQUE KEY 1062)
            if ($conn->errno == 1062) { 
                 $error_message = "El correo ya se encuentra registrado.";
            } 

            // 🚨 ERROR: Redirección Absoluta a registro.php
            $url = build_absolute_url("registro.php", ["error" => urlencode($error_message)]);
            header("Location: " . $url);
            exit();
        }
    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback(); 
        // Si hay una excepción, la mostramos en la URL
        $url = build_absolute_url("registro.php", ["error" => urlencode("Error crítico de PHP: " . $e->getMessage())]);
        header("Location: " . $url);
        exit();
    } finally {
        // Asegurar que cerramos el statement y la conexión
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
    }
} else {
    // Si acceden directamente sin POST, volvemos a registro.php
    header("Location: registro.php");
    exit();
}
// 🚨 NO PONER LA ETIQUETA DE CIERRE ?>