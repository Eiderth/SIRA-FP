<?php
require_once __DIR__. '/Usuario.php';

class Administrador extends Usuario {

    public $type = 'Administrador'; 

    public function __construct($db){
        parent::__construct($db);
    }

    public function listar_usuarios() {
        $stmt = $this->db->query("SELECT id, nombre, rol, estado FROM usuarios ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear_usuario($nombre, $pass, $rol) {
        return $this->insertar('usuarios', [
            'nombre' => $nombre,
            'pass'   => $pass,
            'rol'    => $rol
        ]);
    }

    public function eliminar_usuario($id) {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id LIMIT 1");
        return $stmt->execute(['id' => $id]);
    }

    public function buscar_usuario($nombre, $pass) {
        $stmt = $this->db->prepare("SELECT id, nombre, rol FROM usuarios WHERE nombre = :nombre AND pass = :pass");
        $stmt->execute(['nombre' => $nombre, 'pass' => $pass]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null; 
    }
}