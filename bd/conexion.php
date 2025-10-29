<?php
   
   class Conexion extends PDO {
       private $host = 'localhost';
       private $db   = 'bd_stark_eventos';
       private $user = 'root';
       private $pass = '';

       public function __construct() {
           try {
               parent::__construct("mysql:host=$this->host;dbname=$this->db;charset=utf8mb4", $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
               $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (PDOException $e) {
               echo "Error de conexión: " . $e->getMessage();
           }
       }
   }

?>