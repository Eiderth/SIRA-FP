<?php

class Sistema {
    private $db;
    
    function __construct($db){
        $this->db = $db;
    }

    public function obtener_parametros_formulario() {
        return [
            'periodos' => $this->db->query('SELECT nombre, id FROM periodos_academicos ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'secciones' => $this->db->query('SELECT nombre, id FROM secciones')->fetchAll(PDO::FETCH_ASSOC),
            'grados'  => $this->db->query('SELECT nombre, id FROM grados')->fetchAll(PDO::FETCH_ASSOC),
            'paises'  => $this->db->query('SELECT nombre, id FROM paises')->fetchAll(PDO::FETCH_ASSOC),
            'estados'  => $this->db->query('SELECT * FROM estados')->fetchAll(PDO::FETCH_ASSOC),
            'municipios' => $this->db->query('SELECT * FROM municipios')->fetchAll(PDO::FETCH_ASSOC),
            'ciudades' => $this->db->query('SELECT * FROM ciudades')->fetchAll(PDO::FETCH_ASSOC),
            'parroquias' => $this->db->query('SELECT * FROM parroquias')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function crear_primer_admin($nombre, $pass) {
        $tabla_llena = ($this->db->query("SELECT 1 FROM usuarios LIMIT 1")->fetch());

        if ($tabla_llena) {
            return false; 
        }   

        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, pass, rol) VALUES (:nombre, :pass, :rol)");

        $resultado = $stmt->execute([
            'nombre'=> $nombre,
            'pass' => $pass,
            'rol' => 'Administrador'
        ]);

        if($resultado) {
            return $this->db->lastInsertId();
        }
    }

    public function buscar_usuario($nombre, $pass) {
        $stmt = $this->db->prepare("SELECT id, nombre, rol FROM usuarios WHERE nombre = :nombre AND pass = :pass");
        $stmt->execute(['nombre' => $nombre, 'pass' => $pass]);
        return $stmt->fetch();
    }

}
?>