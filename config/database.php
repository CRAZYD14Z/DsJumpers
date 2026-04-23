<?php
class Database {
    //private $host = "localhost";
    //private $db_name = "juls";
    //private $username = "root";
    //private $password = "";

    private $host = HOST;
    private $db_name = DB_NAME;
    private $username = USERNAME;
    private $password = PASSWORD;    

    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection(){
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        }catch(PDOException $exception){
            // En un entorno de producción, registra este error, no lo muestres al cliente
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>