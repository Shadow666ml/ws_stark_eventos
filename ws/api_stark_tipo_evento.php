<?php
include "../bd/conexion.php";

$pdo = new Conexion();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_tipo_evento'])) {
        $id_tipo_evento = $_GET['id_tipo_evento'];
        $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_tipo_evento = :id_tipo_evento");
        $stmt->bindParam(':id_tipo_evento', $id_tipo_evento);
        $stmt->execute();
        $datosTipoEvento = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($datosTipoEvento) {
            header("HTTP/1.1 200 OK");
            echo json_encode(['success' => true, 'data' => $datosTipoEvento]);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Evento no encontrado']);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento");
        $stmt->execute();
        $evento_contacto = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode($evento_contacto);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        
        $sql = "INSERT INTO stark_eventos.stark_tipo_evento
            (nombre_evento,
             codigo_evento,
             descripcion_evento,
             estado, 
             aud_usr_registro,
             aud_fec_registro)
            VALUES     (:nombre_evento,
                        :codigo_evento,
                        :descripcion_evento,
                        'VIGENTE',
                        :aud_usr_registro,
                        NOW()); ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre_evento', $_POST['nombre_evento']);
        $stmt->bindValue(':codigo_evento', $_POST['codigo_evento']);
        $stmt->bindValue(':descripcion_evento', $_POST['descripcion_evento']); 
        $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
        $stmt->execute();
        $idPost = $pdo->lastInsertId();
        
        if ($idPost) {
            header("HTTP/1.1 201 Tipo evento creado");
            echo json_encode(['success' => true, 'id' => $idPost]);
            exit;
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al crear el tipo evento']);
            exit;
        }
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al crear el tipo evento: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    try {
        // Actualizar el tipo de evento
        if (isset($_GET['id_tipo_evento'])) {
            $id_tipo_evento = $_GET['id_tipo_evento'];

            // Verificar que el tipo de evento exista
            $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_tipo_evento = :id_tipo_evento");
            $stmt->bindParam(':id_tipo_evento', $id_tipo_evento);
            $stmt->execute();
            $dataTipoEvento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dataTipoEvento) {
                // Preparar actualización
                $sql = "UPDATE stark_eventos.stark_tipo_evento
                        SET codigo_evento = :codigo_evento,
                            nombre_evento = :nombre_evento,
                            descripcion_evento = :descripcion_evento,
                            estado = :estado,
                            aud_usr_modificacion = :aud_usr_modificacion,
                            aud_fec_modificacion = NOW()
                        WHERE id_tipo_evento = :id_tipo_evento";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':codigo_evento', $_GET['codigo_evento'] ?? '');
                $stmt->bindValue(':nombre_evento', $_GET['nombre_evento'] ?? '');
                $stmt->bindValue(':descripcion_evento', $_GET['descripcion_evento'] ?? '');
                $stmt->bindValue(':estado', $_GET['estado'] ?? 'VIGENTE');
                $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion'] ?? '');
                $stmt->bindValue(':id_tipo_evento', $id_tipo_evento);

                if ($stmt->execute()) {
                    header("HTTP/1.1 200 OK");
                    echo json_encode(['success' => true, 'message' => 'Tipo de evento actualizado correctamente']);
                    exit;
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['success' => false, 'error' => 'Error al actualizar el tipo de evento']);
                    exit;
                }
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['success' => false, 'error' => 'Tipo de evento no encontrado']);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['success' => false, 'error' => 'ID Tipo Evento no proporcionado']);
            exit;
        }
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['success' => false, 'error' => 'Error al actualizar el tipo de evento: ' . $e->getMessage()]);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Eliminar una empresa
    if (isset($_GET['id_tipo_evento'])) {
        $id_tipo_evento = $_GET['id_tipo_evento'];
        $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_tipo_evento = :id_tipo_evento");
        $stmt->bindParam(':id_tipo_evento', $id_tipo_evento);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            if (isset($_GET['id_tipo_evento'])) {
                $id_tipo_evento = $_GET['id_tipo_evento'];
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_tipo_evento WHERE id_tipo_evento = :id_tipo_evento");
                $stmt->bindParam(':id_tipo_evento', $id_tipo_evento);
                if ($stmt->execute()) {
                    header("HTTP/1.1 200 OK");
                    echo json_encode(['success' => 'Evento eliminado correctamente']);
                    exit;
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al eliminar el evento']);
                    exit;
                }
            } else {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(['error' => 'Id evento no proporcionado']);
                exit;
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Evento no encontrada']);
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => 'ID evento no proporcionado']);
        exit;
    }


}

?>