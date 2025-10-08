<?php
include "../bd/conexion.php";

$pdo = new Conexion();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_tipo_evento'])) {
        $id_tipo_evento = $_GET['id_tipo_evento'];
        $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_tipo_evento = :id_tipo_evento");
        $stmt->bindParam(':id_tipo_evento', $id_tipo_evento);
        $stmt->execute();
        $evento_contacto = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($evento_contacto) {
            header(header: "HTTP/1.1 200 OK");
            echo json_encode($evento_contacto);
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
            echo json_decode($idPost);
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
        // Actualizar una empresa
        if (isset($_GET['id_evento'])) {
            $id_evento = $_GET['id_evento'];
            $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_evento = :id_evento");
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($empresa) {
                $sql = "UPDATE stark_eventos.stark_tipo_evento
                            SET    fecha_evento = :fecha_evento,
                                   estado_evento = :estado_evento,
                                   hora_inicio = :hora_inicio,
                                   hora_fin = :hora_fin,
                                   direccion = :direccion,
                                   numero_invitados = :numero_invitados,
                                   presupuesto_estimado = :presupuesto_estimado,
                                   tematica_evento = :tematica_evento,
                                   comentarios = :comentarios,
                                   lista_servicios = :lista_servicios,
                                   aud_usr_modificacion = :aud_usr_modificacion,
                                   aud_fec_modificacion = NOW()
                            WHERE  id_evento = :id_evento; ";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':fecha_evento', $_GET['fecha_evento']);
                $stmt->bindValue(':estado_evento', $_GET['estado_evento']);
                $stmt->bindValue(':hora_inicio', $_GET['hora_inicio']);
                $stmt->bindValue(':hora_fin', $_GET['hora_fin']);
                $stmt->bindValue(':direccion', $_GET['direccion']);
                $stmt->bindValue(':numero_invitados', $_GET['numero_invitados']);
                $stmt->bindValue(':presupuesto_estimado', $_GET['presupuesto_estimado']);
                $stmt->bindValue(':tematica_evento', $_GET['tematica_evento']);
                $stmt->bindValue(':comentarios', $_GET['comentarios']);
                $stmt->bindValue(':lista_servicios', $_GET['lista_servicios']);
                $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                $stmt->bindValue(':id_evento', $_GET['id_evento']);


                if ($stmt->execute()) {

                    header("HTTP/1.1 200 OK");
                    echo json_encode(['message' => 'Evento actualizado correctamente']);
                    exit;
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al actualizar el evento']);
                    exit;
                }
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['error' => 'Evento no encontrada']);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'ID Evento no proporcionado']);
            exit;
        }


    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al actualizar la empresa: ' . $e->getMessage()]);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Eliminar una empresa
    if (isset($_GET['id_evento'])) {
        $id_evento = $_GET['id_evento'];
        $stmt = $pdo->prepare("SELECT * FROM stark_tipo_evento WHERE id_evento = :id_evento");
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            if (isset($_GET['id_evento'])) {
                $id_evento = $_GET['id_evento'];
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_tipo_evento WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                if ($stmt->execute()) {
                    header("HTTP/1.1 200 OK");
                    echo json_encode(['message' => 'Evento eliminado correctamente']);
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