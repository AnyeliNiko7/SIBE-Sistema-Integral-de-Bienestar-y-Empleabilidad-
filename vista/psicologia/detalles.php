<?php
// Agregar estas líneas al inicio para manejar errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../../config/conexion.php';

// Verificar que la conexión se estableció correctamente
if ($enlace->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Consulta principal
    $query = "SELECT rp.dni, rp.apellidos_nombres AS nombre, rp.edad, rp.motivo_consulta, rp.antecedentes, 
                rp.direccion, rp.telefono, rp.vive_con, rp.correo_estudiantil, rp.sesiones, rp.tratamiento, 
                rp.foto_evidencia, 
                c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno
            FROM registros_psicologia rp
            JOIN carrera c ON rp.id_carrera = c.id_carrera
            JOIN semestres s ON rp.id_semestre = s.id_semestre
            JOIN turnos t ON rp.id_turno = t.id_turno
            WHERE rp.id = ?";
    
    $stmt = $enlace->prepare($query);
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $enlace->error]);
        exit();
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $registro = $result->fetch_assoc();
        
        // Consultar citas
        $historialCitas = [];
        $queryCitas = "SELECT fecha, hora, estado FROM gestion_citas WHERE paciente_id = ?";
        if ($stmtCitas = $enlace->prepare($queryCitas)) {
            $stmtCitas->bind_param("i", $id);
            if ($stmtCitas->execute()) {
                $resultCitas = $stmtCitas->get_result();
                while ($cita = $resultCitas->fetch_assoc()) {
                    $historialCitas[] = $cita;
                }
            }
            $stmtCitas->close();
        }
        
        // Consultar actividades
        $actividadesRealizadas = [];
        $queryActividades = "SELECT descripcion, fecha_creacion FROM actividades_realizadas WHERE paciente_id = ?";
        if ($stmtActividades = $enlace->prepare($queryActividades)) {
            $stmtActividades->bind_param("i", $id);
            if ($stmtActividades->execute()) {
                $resultActividades = $stmtActividades->get_result();
                while ($actividad = $resultActividades->fetch_assoc()) {
                    $actividadesRealizadas[] = $actividad;
                }
            }
            $stmtActividades->close();
        }
        
        // Devolver datos como JSON
        header('Content-Type: application/json');
        echo json_encode([
            'dni' => $registro['dni'],
            'nombre' => $registro['nombre'],
            'edad' => $registro['edad'],
            'carrera' => $registro['carrera'],
            'semestre' => $registro['semestre'],
            'turno' => $registro['turno'],
            'direccion' => $registro['direccion'],
            'telefono' => $registro['telefono'],
            'vive_con' => $registro['vive_con'],
            'correo_estudiantil' => $registro['correo_estudiantil'],
            'sesiones' => $registro['sesiones'],
            'motivo_consulta' => $registro['motivo_consulta'],
            'antecedentes' => $registro['antecedentes'],
            'tratamiento' => $registro['tratamiento'],
            'foto_evidencia' => $registro['foto_evidencia'],
            'historial_citas' => $historialCitas,
            'actividades_realizadas' => $actividadesRealizadas,
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontró el registro.']);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID no proporcionado.']);
}

$enlace->close();
?>