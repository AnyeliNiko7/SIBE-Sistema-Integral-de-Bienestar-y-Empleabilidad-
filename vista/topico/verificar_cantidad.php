<?php
require_once '../../config/conexion.php';

if (isset($_GET['id_medicamento'])) {
    $id_medicamento = intval($_GET['id_medicamento']);

    $query = "SELECT cantidad FROM inventario_medicamentos WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $id_medicamento);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    echo json_encode(['cantidad_disponible' => $data['cantidad'] ?? 0]);
}
