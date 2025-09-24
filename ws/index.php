<?php 
    include "../bd/conexion.php";

    $pdo = new Conexion();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') 
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM stark_usuario WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                header("HTTP/1.1 200 OK");
                echo json_encode($usuario);
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM stark_usuario");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            header("HTTP/1.1 200 OK");
            echo json_encode($usuarios);
            exit;
        }
    } 

    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        try{
        $sql = "INSERT INTO stark_eventos.stark_usuario
                    (cod_usuario, tipo_documento, num_documento, nombre_completo, apellido_paterno, apellido_materno, gls_correo, password, tipo_usuario, aud_usr_registro, aud_fec_registro)
                    VALUES(:cod_usuario, :tipo_documento, :num_documento, :nombre_completo, :apellido_paterno, :apellido_materno, :gls_correo, aes_encrypt(:password,'AES'), :tipo_usuario, :aud_usr_registro, :aud_fec_registro);
                ";
            
            //echo $sql;

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':cod_usuario', $_POST['cod_usuario']);
            $stmt->bindValue(':tipo_documento', $_POST['tipo_documento']);
            $stmt->bindValue(':num_documento', $_POST['num_documento']);
            $stmt->bindValue(':nombre_completo', $_POST['nombre_completo']);
            $stmt->bindValue(':apellido_paterno', $_POST['apellido_paterno']);
            $stmt->bindValue(':apellido_materno', $_POST['apellido_materno']);
            $stmt->bindValue(':gls_correo', $_POST['gls_correo']);
            $stmt->bindValue(':password', $_POST['password']);
            $stmt->bindValue(':tipo_usuario', $_POST['tipo_usuario']);
            $stmt->bindValue(':aud_usr_registro', $_POST['aud_usr_registro']);
            $stmt->bindValue(':aud_fec_registro', $_POST['aud_fec_registro']);
            $stmt->execute();
            $idPost = $pdo->lastInsertId();
            if($idPost)
            {
                header("HTTP/1.1 201 Usuario Creado");
                echo json_decode($idPost);
                exit;
            }
        }
        catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') 
    {
        try{
            $sql = "UPDATE stark_eventos.stark_usuario
                    SET   cod_usuario=:cod_usuario
                        , tipo_documento=:tipo_documento
                        , num_documento=:num_documento
                        , nombre_completo=:nombre_completo
                        , apellido_paterno=:apellido_paterno
                        , apellido_materno=:apellido_materno
                        , gls_correo=:gls_correo
                        , password=:password
                        , tipo_usuario=:tipo_usuario
                        , aud_usr_modificacion=:aud_usr_modificacion
                        , aud_fec_modificacion=:aud_fec_modificacion
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':cod_usuario', $_GET['cod_usuario']);
            $stmt->bindValue(':tipo_documento', $_GET['tipo_documento']);
            $stmt->bindValue(':num_documento', $_GET['num_documento']);
            $stmt->bindValue(':nombre_completo', $_GET['nombre_completo']);
            $stmt->bindValue(':apellido_paterno', $_GET['apellido_paterno']);
            $stmt->bindValue(':apellido_materno', $_GET['apellido_materno']);
            $stmt->bindValue(':gls_correo', $_GET['gls_correo']);
            $stmt->bindValue(':password', $_GET['password']);
            $stmt->bindValue(':tipo_usuario', $_GET['tipo_usuario']);
            $stmt->bindValue(':aud_usr_modificacion', $_GET['aud_usr_modificacion']);
            $stmt->bindValue(':aud_fec_modificacion', $_GET['aud_fec_modificacion']);
            $stmt->bindValue(':id', $_GET['id']);
            
            
            if ($stmt->execute()) {
                
                header("HTTP/1.1 200 OK");
                echo json_encode(['message' => 'Usuario actualizado correctamente']);
                exit;
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(['error' => 'Error al actualizar el stark_usuario']);
                exit;
            }
        }
        catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
            exit;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') 
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM stark_eventos.stark_usuario WHERE id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                header("HTTP/1.1 200 OK");
                echo json_encode(['message' => 'Usuario eliminado correctamente']);
                exit;
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(['error' => 'Error al eliminar el usuario']);
                exit;
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'ID de usuario no proporcionado']);
            exit;
        }
    }

?>