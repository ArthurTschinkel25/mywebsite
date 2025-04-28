<?php
class Database {
    private $hostname = "localhost";
    private $bancodedados = "clientes";
    private $usuario = "root";
    private $senha = "";
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli(
            $this->hostname,
            $this->usuario,
            $this->senha,
            $this->bancodedados
        );

        if ($this->mysqli->connect_errno) {
            die("Falha ao conectar: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset("utf8mb4");
    }

    public function getMysqli() {
        return $this->mysqli;
    }
}
?>