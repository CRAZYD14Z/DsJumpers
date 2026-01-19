<?php
class Database {
    //private $host = "localhost";
    //private $db_name = "juls";
    //private $username = "root";
    //private $password = "";

    private $host = "162.215.249.40";
    private $db_name = "bbbm8m0_dsjumpers";
    private $username = "bbbm8m0_root";
    private $password = "8Ej4eh7!5~az+6c";    

    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection(){
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            // En un entorno de producción, registra este error, no lo muestres al cliente
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>