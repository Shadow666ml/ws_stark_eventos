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
             se.aud_usr_modificacion,
                se.aud_fec_modificacion,
             se.id_proveedor,
             sp.nombre_proveedor,
             sec.nombre_contacto,
                sec.correo_contacto,
                sec.telefono_contacto,
                sem.nombre_novio,
                sem.nombre_novia,
                sem.lugar_recepcion,
                sem.lugar_ceremonia,
                secu.nombre_cumpleaniero,
                secu.edad_cumpleaniero,
                seb.nombre_padre,
                seb.nombre_madre,
                seb.nombre_bebe,
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
        LEFT JOIN stark_proveedor sp ON se.id_proveedor = sp.id_proveedor
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
                        :presupuesto_estimado,
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
        $stmt->bindValue(':presupuesto_estimado', $_POST['presupuesto_estimado']);

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
                            lugar_ceremonia,
                            lugar_recepcion,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_novio,
                            :nombre_novia,
                            :lugar_ceremonia,
                            :lugar_recepcion,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_novio', $_POST['nombre_novio']);
                $stmt->bindValue(':nombre_novia', $_POST['nombre_novia']);
                $stmt->bindValue(':lugar_ceremonia', $_POST['lugar_ceremonia']);
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
                            nombre_bebe,
                            sexo_bebe,
                            lista_regalos_sugeridos,
                            aud_usr_registro,
                            aud_fec_registro)
                VALUES     ($idPost,
                            :nombre_padre,
                            :nombre_madre,
                            :nombre_bebe,
                            :sexo_bebe,
                            :lista_regalos_sugeridos,
                            :aud_usr_registro,
                            now()); ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_padre', $_POST['nombre_padre']);
                $stmt->bindValue(':nombre_madre', $_POST['nombre_madre']);
                $stmt->bindValue(':nombre_bebe', $_POST['nombre_bebe']);
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
        if (isset($_GET['id_evento'])) {
            $id_evento = $_GET['id_evento'];

            // Verificar que el evento exista
            $stmt = $pdo->prepare("SELECT * FROM stark_eventos WHERE id_evento = :id_evento");
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($evento) {

                // 🔹 Actualizar tabla principal
                $sql = "UPDATE stark_eventos.stark_eventos
                        SET fecha_evento = :fecha_evento,
                            estado_evento = :estado_evento,
                            hora_inicio = :hora_inicio,
                            hora_fin = :hora_fin,
                            direccion = :direccion,
                            numero_invitados = :numero_invitados,
                            presupuesto_estimado = :presupuesto_estimado,
                            tematica_evento = :tematica_evento,
                            comentarios = :comentarios,
                            lista_servicios = :lista_servicios,
                            id_proveedor = :id_proveedor,
                            aud_usr_modificacion = :aud_usr_modificacion,
                            aud_fec_modificacion = NOW()
                        WHERE id_evento = :id_evento";

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
                $stmt->bindValue(':id_proveedor', $_GET['id_proveedor']);
                $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                $stmt->bindValue(':id_evento', $id_evento);
                $stmt->execute();

                // 🔹 Actualizar contacto del evento
                $sql = "UPDATE stark_eventos.stark_evento_contacto
                        SET nombre_contacto = :nombre_contacto,
                            correo_contacto = :correo_contacto,
                            telefono_contacto = :telefono_contacto,
                            aud_usr_modificacion = :aud_usr_modificacion,
                            aud_fec_modificacion = NOW()
                        WHERE id_evento = :id_evento";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nombre_contacto', $_GET['nombre_contacto']);
                $stmt->bindValue(':correo_contacto', $_GET['correo_contacto']);
                $stmt->bindValue(':telefono_contacto', $_GET['telefono_contacto']);
                $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                $stmt->bindValue(':id_evento', $id_evento);
                $stmt->execute();

                // 🔹 Switch según tipo_evento
                $tipo_evento = $evento['tipo_evento'];

                switch ($tipo_evento) {
                    case 'MATRIMONIO':
                        $sql = "UPDATE stark_eventos.stark_eventos_matrimonio
                                SET nombre_novio = :nombre_novio,
                                    nombre_novia = :nombre_novia,
                                    lugar_recepcion = :lugar_recepcion,
                                    lugar_ceremonia = :lugar_ceremonia,
                                    aud_usr_modificacion = :aud_usr_modificacion,
                                    aud_fec_modificacion = NOW()
                                WHERE id_evento = :id_evento";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':nombre_novio', $_GET['nombre_novio']);
                        $stmt->bindValue(':nombre_novia', $_GET['nombre_novia']);
                        $stmt->bindValue(':lugar_recepcion', $_GET['lugar_recepcion']);
                        $stmt->bindValue(':lugar_ceremonia', $_GET['lugar_ceremonia']);
                        $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                        $stmt->bindValue(':id_evento', $id_evento);
                        $stmt->execute();
                        break;

                    case 'CUMPLEANIOS':
                        $sql = "UPDATE stark_eventos.stark_eventos_cumpleanos
                                SET nombre_cumpleaniero = :nombre_cumpleaniero,
                                    edad_cumpleaniero = :edad_cumpleaniero,
                                    aud_usr_modificacion = :aud_usr_modificacion,
                                    aud_fec_modificacion = NOW()
                                WHERE id_evento = :id_evento";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':nombre_cumpleaniero', $_GET['nombre_cumpleaniero']);
                        $stmt->bindValue(':edad_cumpleaniero', $_GET['edad_cumpleaniero']);
                        $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                        $stmt->bindValue(':id_evento', $id_evento);
                        $stmt->execute();
                        break;

                    case 'BABYSHOWER':
                        $sql = "UPDATE stark_eventos.stark_eventos_baby_shower
                                SET nombre_padre = :nombre_padre,
                                    nombre_madre = :nombre_madre,
                                    sexo_bebe = :sexo_bebe,
                                    nombre_bebe = :nombre_bebe,
                                    lista_regalos_sugeridos = :lista_regalos_sugeridos,
                                    aud_usr_modificacion = :aud_usr_modificacion,
                                    aud_fec_modificacion = NOW()
                                WHERE id_evento = :id_evento";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':nombre_padre', $_GET['nombre_padre']);
                        $stmt->bindValue(':nombre_madre', $_GET['nombre_madre']);
                        $stmt->bindValue(':sexo_bebe', $_GET['sexo_bebe']);
                        $stmt->bindValue(':nombre_bebe', $_GET['nombre_bebe']);
                        $stmt->bindValue(':lista_regalos_sugeridos', $_GET['lista_regalos_sugeridos']);
                        $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                        $stmt->bindValue(':id_evento', $id_evento);
                        $stmt->execute();
                        break;

                    case 'QUINCEANIERO':
                        $sql = "UPDATE stark_eventos.stark_eventos_quinceanero
                                SET nombre_quinceanera = :nombre_quinceanera,
                                    fec_nacimiento_quinceanera = :fec_nacimiento_quinceanera,
                                    aud_usr_modificacion = :aud_usr_modificacion,
                                    aud_fec_modificacion = NOW()
                                WHERE id_evento = :id_evento";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':nombre_quinceanera', $_GET['nombre_quinceanera']);
                        $stmt->bindValue(':fec_nacimiento_quinceanera', $_GET['fec_nacimiento_quinceanera']);
                        $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                        $stmt->bindValue(':id_evento', $id_evento);
                        $stmt->execute();
                        break;
                }

                header("HTTP/1.1 200 OK");
                echo json_encode(['success' => true, 'message' => 'Evento actualizado correctamente']);
                exit;

            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['error' => 'Evento no encontrado']);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'ID Evento no proporcionado']);
            exit;
        }
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al actualizar el evento: ' . $e->getMessage()]);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if (isset($_GET['id_evento'])) {
        $id_evento = $_GET['id_evento'];

        // Verificar que el evento exista
        $stmt = $pdo->prepare("SELECT * FROM stark_eventos WHERE id_evento = :id_evento");
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($evento) {
            try {
                // 🔹 Iniciar transacción para consistencia
                $pdo->beginTransaction();

                // 🔹 Eliminar de tablas hijas primero
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_evento_contacto WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos_matrimonio WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos_cumpleanos WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos_baby_shower WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos_quinceanero WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                // 🔹 Finalmente eliminar el evento principal
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_eventos WHERE id_evento = :id_evento");
                $stmt->bindParam(':id_evento', $id_evento);
                $stmt->execute();

                // 🔹 Confirmar transacción
                $pdo->commit();

                header("HTTP/1.1 200 OK");
                echo json_encode(['success' => true, 'message' => 'Evento y registros relacionados eliminados correctamente']);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(['error' => 'Error al eliminar los registros: ' . $e->getMessage()]);
                exit;
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Evento no encontrado']);
            exit;
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => 'ID Evento no proporcionado']);
        exit;
    }
}

?>