<?php
require 'db.php';

// Polyfill para compatibilidad con PHP < 8.0
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        if ('' === $needle) return true;
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Tu lógica de dominios permitidos se mantiene
    $dominios_permitidos = ["@alu.uct.cl", "@uct.cl"];
    $valido = false;
    foreach ($dominios_permitidos as $dominio) {
        if (str_ends_with($email, $dominio)) {
            $valido = true;
            break;
        }
    }
    if (!$valido) {
        header("Location: index.php?error=Correo no válido. Usa tu correo institucional.");
        exit();
    }

    // Consulta a la BD actualizada para obtener también la contraseña y el id_curso del estudiante
    $stmt = $conn->prepare("SELECT id, nombre, password, id_equipo, es_docente, id_curso FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        // --- LÓGICA DE VERIFICACIÓN DE CONTRASEÑA ---
        if ($usuario['es_docente']) {
            $password_ingresada = $_POST['password'] ?? '';
            
            if ($usuario['password'] === null || !password_verify($password_ingresada, $usuario['password'])) {
                header("Location: index.php?error=Correo o contraseña incorrectos.");
                exit();
            }
        }

        // Si la verificación fue exitosa (o no fue necesaria), creamos la sesión.
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['id_equipo'] = $usuario['id_equipo'];
        $_SESSION['es_docente'] = $usuario['es_docente'];
        
        // Si el usuario es estudiante, guardamos su id_curso directamente en la sesión
        // Los docentes lo establecerán en select_course.php
        if (!$usuario['es_docente']) {
             $_SESSION['id_curso_activo'] = $usuario['id_curso'];
        }

        // --- NUEVA LÓGICA DE REDIRECCIÓN SEGÚN ROL ---
        if ($usuario['es_docente']) {
            // REDIRECCIÓN CLAVE: El docente debe ir a seleccionar su curso
            header("Location: select_course.php");
        } else {
            // El estudiante va directo a su dashboard (asumiendo que ya tiene un curso asignado)
            header("Location: dashboard_estudiante.php");
        }
        exit();

    } else {
        header("Location: index.php?error=El correo no está registrado en el sistema.");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>