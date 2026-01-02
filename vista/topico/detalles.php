<?php
require_once '../../config/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "
        SELECT rt.dni, rt.nombre, rt.edad, c.nombre AS carrera, 
               s.nombre AS semestre, t.nombre AS turno, rt.sintomas
        FROM registros_topico rt
        JOIN carrera c ON rt.id_carrera = c.id_carrera
        JOIN semestres s ON rt.id_semestre = s.id_semestre
        JOIN turnos t ON rt.id_turno = t.id_turno
        WHERE rt.id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $registro = $result->fetch_assoc();
        $queryMedicamentos = "
            SELECT m.nombre
            FROM registros_medicamentos rm
            JOIN inventario_medicamentos m ON rm.id_medicamento = m.id
            WHERE rm.id_registro_topico = ?";
        $stmtMedicamentos = $enlace->prepare($queryMedicamentos);
        $stmtMedicamentos->bind_param("i", $id);
        $stmtMedicamentos->execute();
        $resultMedicamentos = $stmtMedicamentos->get_result();

        $medicamentos = [];
        while ($medicamento = $resultMedicamentos->fetch_assoc()) {
            $medicamentos[] = $medicamento;
        }
        echo json_encode(array_merge($registro, ['medicamentos' => $medicamentos]));
    } else {
        echo json_encode(['error' => 'No se encontró el registro.']);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado.']);
}
?>
