<?php
include "../bd/conexion.php";

$pdo = new Conexion();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_empresa'])) {
        $id_empresa = $_GET['id_empresa'];
        $stmt = $pdo->prepare("SELECT * FROM stark_empresa WHERE id_empresa = :id_empresa");
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            header("HTTP/1.1 200 OK");
            echo json_encode($empresa);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Empresa no encontrada']);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM stark_empresa");
        $stmt->execute();
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode($empresas);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO stark_eventos.stark_empresa
            (gls_empresa,
             direccion,
             telefono,
             correo,
             aud_usr_registro,
             aud_fec_registro)
             VALUES     
            (:gls_empresa,
            :direccion,
            :telefono,
            :correo,
            :aud_usr_registro,
            NOW()); ";

        //echo $sql;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':gls_empresa', $_POST['gls_empresa']);
        $stmt->bindValue(':direccion', $_POST['direccion']);
        $stmt->bindValue(':telefono', $_POST['telefono']);
        $stmt->bindValue(':correo', $_POST['correo']);
        $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
        $stmt->execute();
        $idPost = $pdo->lastInsertId();
        if ($idPost) {
            header("HTTP/1.1 201 Empresa Creada");
            echo json_decode($idPost);
            exit;
        }
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Error al crear la empresa: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    try {
        // Actualizar una empresa
        if (isset($_GET['id_empresa'])) {
            $id_empresa = $_GET['id_empresa'];
            $stmt = $pdo->prepare("SELECT * FROM stark_empresa WHERE id_empresa = :id_empresa");
            $stmt->bindParam(':id_empresa', $id_empresa);
            $stmt->execute();
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($empresa) {
                $sql = "UPDATE stark_eventos.stark_empresa
                    SET    gls_empresa = :gls_empresa,
                           direccion = :direccion,
                           telefono = :telefono,
                           correo = :correo,
                           aud_usr_modificacion = :aud_usr_modificacion,
                           aud_fec_modificacion = Now()
                    WHERE  id_empresa = :id_empresa; ";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':gls_empresa', $_GET['gls_empresa']);
                $stmt->bindValue(':direccion', $_GET['direccion']);
                $stmt->bindValue(':telefono', $_GET['telefono']);
                $stmt->bindValue(':correo', $_GET['correo']);
                $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
                $stmt->bindValue(':id_empresa', $_GET['id_empresa']);


                if ($stmt->execute()) {

                    header("HTTP/1.1 200 OK");
                    echo json_encode(['message' => 'Empresa actualizada correctamente']);
                    exit;
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al actualizar la empresa']);
                    exit;
                }
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['error' => 'Empresa no encontrada']);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'ID de empresa no proporcionado']);
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
    if (isset($_GET['id_empresa'])) {
        $id_empresa = $_GET['id_empresa'];
        $stmt = $pdo->prepare("SELECT * FROM stark_empresa WHERE id_empresa = :id_empresa");
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            if (isset($_GET['id_empresa'])) {
                $id_empresa = $_GET['id_empresa'];
                $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_empresa WHERE id_empresa = :id_empresa");
                $stmt->bindParam(':id_empresa', $id_empresa);
                if ($stmt->execute()) {
                    header("HTTP/1.1 200 OK");
                    echo json_encode(['message' => 'Empresa eliminada correctamente']);
                    exit;
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo json_encode(['error' => 'Error al eliminar la empresa']);
                    exit;
                }
            } else {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(['error' => 'ID de empresa no proporcionado']);
                exit;
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Empresa no encontrada']);
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => 'ID de empresa no proporcionado']);
        exit;
    }


}

?>