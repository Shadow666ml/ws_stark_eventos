<?php
include "../bd/conexion.php";

$pdo = new Conexion();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_evento'])) {
        $id_evento = $_GET['id_evento'];
        $stmt = $pdo->prepare("SELECT se.fecha_evento,
             se.estado_evento,
             se.tipo_evento, 
             se.hora_inicio,
             se.hora_fin,
             se.direccion,
             se.numero_invitados,
             se.presupuesto_estimado,
             se.tematica_evento,
             se.comentarios,
             se.lista_servicios,
             se.aud_usr_registro,
             se.aud_fec_registro  , 
             sec.nombre_contacto,
                sec.correo_contacto,
                sec.telefono_contacto,
                sem.nombre_novio,
                sem.nombre_novia,
                sem.lugar_recepcion,
                secu.nombre_cumpleaniero,
                secu.edad_cumpleaniero,
                seb.nombre_padre,
                seb.nombre_madre,
                seb.sexo_bebe,
                seb.lista_regalos_sugeridos,
                seq.nombre_quinceanera,
                seq.fec_nacimiento_quinceanera
        FROM stark_eventos se
        JOIN stark_evento_contacto sec ON se.id_evento = sec.id_evento
        LEFT JOIN stark_eventos_matrimonio sem ON se.id_evento = sem.id_evento
        LEFT JOIN stark_eventos_cumpleanos secu ON se.id_evento = secu.id_evento
        LEFT JOIN stark_eventos_baby_shower seb ON se.id_evento = seb.id_evento
        LEFT JOIN stark_eventos_quinceanero seq ON se.id_evento = seq.id_evento
        WHERE se.id_evento = :id_evento");
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            header("HTTP/1.1 200 OK");
            echo json_encode($empresa);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Evento no encontrado']);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM stark_eventos");
        $stmt->execute();
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode($empresas);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {

        $tipo_evento = $_POST['tipo_evento'];
        
        $sql = "INSERT INTO stark_eventos.stark_eventos
            (fecha_evento,
             estado_evento,
             tipo_evento, 
             hora_inicio,
             hora_fin,
             direccion,
             numero_invitados,
             presupuesto_estimado,
             tematica_evento,
             comentarios,
             lista_servicios,
             aud_usr_registro,
             aud_fec_registro)
            VALUES     (:fecha_evento,
                        'PENDIENTE ASIGNACION',
                        :tipo_evento,
                        :hora_inicio,
                        :hora_fin,
                        :direccion,
                        :numero_invitados,
                        NULL,
                        :tematica_evento,
                        :comentarios,
                        NULL,
                        :aud_usr_registro,
                        NOW()); ";
       
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':fecha_evento', $_POST['fecha_evento']);
        $stmt->bindValue(':tipo_evento', $_POST['tipo_evento']);
        $stmt->bindValue(':hora_inicio', $_POST['hora_inicio']);
        $stmt->bindValue(':hora_fin', $_POST['hora_fin']);
        $stmt->bindValue(':direccion', $_POST['direccion']);
        $stmt->bindValue(':numero_invitados', $_POST['numero_invitados']);
        $stmt->bindValue(':tematica_evento', $_POST['tematica_evento']);
        $stmt->bindValue(':comentarios', $_POST['comentarios']);
        $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
        $stmt->execute();
        $idPost = $pdo->lastInsertId();

        
        //Ini Insertar EventoContacto 
        $sql="INSERT INTO stark_eventos.stark_evento_contacto
            (id_evento,
             nombre_contacto,
             correo_contacto,
             telefono_contacto,
             aud_usr_registro,
             aud_fec_registro)
        VALUES     ($idPost,
                    :nombre_contacto,
                    :correo_contacto,
                    :telefono_contacto,
                    :aud_usr_registro,
                    now()); ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nombre_contacto', $_POST['nombre_contacto']);
        $stmt->bindValue(':correo_contacto', $_POST['correo_contacto']);
        $stmt->bindValue(':telefono_contacto', $_POST['telefono_contacto']);
        $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
        $stmt->execute();

        $id_contacto = $pdo->lastInsertId();
        if (!$id_contacto) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al crear el contacto del evento']);
            exit;
        }
        //Fin Insertar EventoContacto

        switch ($tipo_evento) {
            case 'MATRIMONIO':
                // Lógica específica para MATRIMONIO
                //Ini Insertar EventoCumpleanos
                $sql = "INSERT INTO stark_eventos.stark_eventos_matrimonio
                            (id_evento,
                            nombre_novio,
                            nombre_novia,
                            lugar_recepcion,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_novio,
                            :nombre_novia,
                            :lugar_recepcion,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_novio', $_POST['nombre_novio']);
                $stmt->bindValue(':nombre_novia', $_POST['nombre_novia']);
                $stmt->bindValue(':lugar_recepcion', $_POST['lugar_recepcion']);
                $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
                $stmt->execute();
                $id_matrimonio = $pdo->lastInsertId();
                if (!$id_matrimonio) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al crear el registro de matrimonio del evento']);
                    exit;
                }
                //Fin Insertar EventoMatrimonio
                break;
            case 'CUMPLEANIOS':
                // Lógica específica para CUMPLEAÑOS
                //Ini Insertar EventoCumpleanos
                $sql = "INSERT INTO stark_eventos.stark_eventos_cumpleanos
                            (id_evento,
                            nombre_cumpleaniero,
                            edad_cumpleaniero,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_cumpleaniero,
                            :edad_cumpleaniero,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_cumpleaniero', $_POST['nombre_cumpleaniero']);
                $stmt->bindValue(':edad_cumpleaniero', $_POST['edad_cumpleaniero']);
                $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
                $stmt->execute();
                $id_cumpleanos = $pdo->lastInsertId();
                if (!$id_cumpleanos) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al crear el registro de cumpleaños del evento']);
                    exit;
                }
                //Fin Insertar EventoCumpleanos
                break;
            case 'BABYSHOWER':
                // Lógica específica para BABYSHOWER
                //Ini Insertar EventoBabyShower
                $sql = "INSERT INTO stark_eventos.stark_eventos_baby_shower
                            (id_evento,
                            nombre_padre,
                            nombre_madre,
                            sexo_bebe,
                            lista_regalos_sugeridos,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_padre,
                            :nombre_madre,
                            :sexo_bebe,
                            :lista_regalos_sugeridos,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_padre', $_POST['nombre_padre']);
                $stmt->bindValue(':nombre_madre', $_POST['nombre_madre']);
                $stmt->bindValue(':sexo_bebe', $_POST['sexo_bebe']);
                $stmt->bindValue(':lista_regalos_sugeridos', $_POST['lista_regalos_sugeridos']);
                $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
                $stmt->execute();
                $id_baby_shower = $pdo->lastInsertId();
                if (!$id_baby_shower) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al crear el registro de baby shower del evento']);
                    exit;
                }
                //Fin Insertar EventoBabyShower
                
                break;
            case 'QUINCEANIERO':
                // Lógica específica para QUINCEAÑERO
                 //Ini Insertar EventoQuinceaniero
                $sql = "INSERT INTO stark_eventos.stark_eventos_quinceanero
                            (id_evento,
                            nombre_quinceanera,
                            fec_nacimiento_quinceanera,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_quinceanera,
                            :fec_nacimiento_quinceanera,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_quinceanera', $_POST['nombre_quinceanera']);
                $stmt->bindValue(':fec_nacimiento_quinceanera', $_POST['fec_nacimiento_quinceanera']);
                $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
                $stmt->execute();
                $id_quinceaniero = $pdo->lastInsertId();
                if (!$id_quinceaniero) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al crear el registro de quinceañero del evento']);
                    exit;
                }
                //Fin Insertar EventoQuinceaniero
                break;
            default:
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(['error' => 'Tipo de evento no válido']);
                exit;
        }

        
        
        if ($idPost) {
            header("HTTP/1.1 201 Evento creado");
            echo json_encode(['success' => true, 'id' => $idPost]);
            exit;
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al crear el evento']);
            exit;
        }
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al crear el evento: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    try {
        // Actualizar una empresa
        if (isset($_GET['id_evento'])) {
            $id_evento = $_GET['id_evento'];
            $stmt = $pdo->prepare("SELECT * FROM stark_eventos WHERE id_evento = :id_evento");
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($empresa) {
                $sql = "UPDATE stark_eventos.stark_eventos
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
        $stmt = $pdo->prepare("SELECT * FROM stark_eventos WHERE id_evento = :id_evento");
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            if (isset($_GET['id_evento'])) {
                $id_evento = $_GET['id_evento'];
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos WHERE id_evento = :id_evento");
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