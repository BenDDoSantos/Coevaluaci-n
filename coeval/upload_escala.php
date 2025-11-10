<?php
require 'db.php';
// Requerir ser docente Y tener un curso activo
verificar_sesion(true, true); 

$id_curso_activo = get_active_course_id();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['escala_csv'])) {
    if ($_FILES['escala_csv']['error'] !== UPLOAD_ERR_OK) {
        header("Location: dashboard_docente.php?status=" . urlencode("Error al subir el archivo de escala."));
        exit();
    }
    $archivo = $_FILES['escala_csv']['tmp_name'];
    $conn->begin_transaction();
    try {
        // PASO 1: ELIMINAR la escala actual SOLAMENTE para el curso activo.
        $stmt_truncate = $conn->prepare("DELETE FROM escala_notas WHERE id_curso = ?");
        $stmt_truncate->bind_param("i", $id_curso_activo);
        $stmt_truncate->execute();
        $stmt_truncate->close();
        
        $fila = 0;
        if (($gestor = fopen($archivo, "r")) !== FALSE) {
            // PASO 2: Insertar la nueva escala, ASIGNANDO el id_curso_activo
            $stmt = $conn->prepare("INSERT INTO escala_notas (puntaje, nota, id_curso) VALUES (?, ?, ?)");
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $fila++;
                // Omitir cabecera (header)
                if ($fila == 1 && !is_numeric(trim($datos[0]))) continue;
                
                // Validar datos (puntaje debe ser INT, nota debe ser FLOAT y permitir coma decimal)
                if (count($datos) >= 2 && is_numeric(trim($datos[0])) && is_numeric(str_replace(',', '.', trim($datos[1])))) {
                    $puntaje = (int)trim($datos[0]);
                    $nota = (float)str_replace(',', '.', trim($datos[1]));
                    
                    $stmt->bind_param("idi", $puntaje, $nota, $id_curso_activo); // 'i': int, 'd': double/float, 'i': int
                    $stmt->execute();
                }
            }
            fclose($gestor);
            $stmt->close();
        }
        $conn->commit();
        header("Location: dashboard_docente.php?status=" . urlencode("Escala de notas actualizada correctamente para el curso activo."));
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: dashboard_docente.php?status=" . urlencode("Error al procesar la escala: " . $e->getMessage()));
    }
    exit();
}
?>