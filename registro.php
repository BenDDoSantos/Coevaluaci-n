<?php
// require 'db.php'; // <--- COMENTA TEMPORALMENTE ESTA LÍNEA

// Redirigir si ya está logueado
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard_docente.php");
    exit();
}

$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$status_message = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header text-white bg-primary text-center">
                        <h4 class="mb-0">Registro de Docentes</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if ($status_message): ?>
                            <div class="alert alert-success"><?php echo $status_message; ?></div>
                        <?php endif; ?>
                        
                        <form action="register_user.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Institucional (@uct.cl)</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <small class="form-text text-muted">Solo se permiten correos @uct.cl (Docentes).</small>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">Registrarse como Docente</button>
                            <p class="text-center">¿Ya tienes cuenta? <a href="index.php">Inicia Sesión</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>