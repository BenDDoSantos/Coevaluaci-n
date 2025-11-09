<?php
session_start();

// Capturar errores enviados por GET
$mensaje_error = isset($_GET['error']) ? $_GET['error'] : '';
$mensaje_exito = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de Sesión - Docentes</title>
  <link rel="stylesheet" href="css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="width: 380px;">
      <h3 class="text-center mb-4 text-primary">Inicio de Sesión</h3>
      <form action="login.php" method="POST" novalidate>
        
        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label">Correo institucional</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="nombre@alu.uct.cl" required>
        </div>

        <!-- Contraseña (solo para docentes) -->
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña">
          <div class="form-text">Solo los docentes deben ingresar contraseña.</div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje_error): ?>
          <div class="alert alert-danger py-2 text-center"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if ($mensaje_exito): ?>
          <div class="alert alert-success py-2 text-center"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>

        <!-- Botón -->
        <div class="d-grid mt-3">
          <button type="submit" class="btn btn-primary">Iniciar sesión</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <small class="text-muted">© 2025 Instituto Tecnológico TEC-UCT</small>
      </div>
    </div>
  </div>

</body>
</html>
