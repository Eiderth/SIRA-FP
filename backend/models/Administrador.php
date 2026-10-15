<?php
require_once __DIR__. '/entities/utils.php';

class Administrador {
    use Utils;

    private $db;
    public $type = 'Administrador';
    public $id; 

    public function __construct($db, $id){
        $this->db = $db;
        $this->id = $id; 
    }
    
    public function listar_usuarios() {
        $stmt = $this->db->query("SELECT id, nombre, rol, estado FROM USUARIO ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear_usuario($nombre, $pass, $rol) {
        $pass_hash = password_hash($pass, PASSWORD_BCRYPT);
        return $this->insertar('USUARIO', [
            'nombre' => $nombre,
            'pass'   => $pass_hash,
            'rol'    => $rol
        ]);
    }

    public function actualizar_usuario($nombre, $rol, $estado, $id) {

        if(($rol !== 'Administrador' || $estado !== 'Activo') && $this->proteger_ultimo_admin($id)) {
            return false;
        }

        $this->actualizar(
            'USUARIO',
            [
                'nombre' => $nombre,
                'rol' => $rol,
                'estado' => $estado
            ],
            'id', $id
        );
        return true;
    }

    private function proteger_ultimo_admin($id){
        $admins_activos = $this->db->query(
            "SELECT id FROM USUARIO WHERE estado = 'Activo' AND rol = 'Administrador'"
        )->fetchAll(PDO::FETCH_COLUMN);
        return count($admins_activos) === 1 && (int)$admins_activos[0] === (int)$id;
    }


    public function eliminar_usuario($id) {
        
        if ($this->proteger_ultimo_admin($id)) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM USUARIO WHERE id = :id LIMIT 1");
        return $stmt->execute(['id' => $id]);
    }

    public function filtrar_usuarios($general, $rol, $estado) {

        $sql = "SELECT id, nombre, rol, estado FROM USUARIO WHERE 1=1";
        $params = [];

        if (!empty($general)) {
            
            if (is_numeric($general)) {
                $sql .= " AND (id = :id_exacto OR nombre LIKE :nombre)";
                $params[':id_exacto'] = $general;
                $params[':nombre'] = '%' . $general . '%';
            } else {
                $sql .= " AND nombre LIKE :nombre";
                $params[':nombre'] = '%' . $general . '%';
            }
        }

        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $rol;
        }

        if (!empty($estado)) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $estado;
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function buscar_usuario($nombre, $pass) {
        $stmt = $this->db->prepare("SELECT id, nombre, rol FROM USUARIO WHERE nombre = :nombre AND pass = :pass");
        $stmt->execute(['nombre' => $nombre, 'pass' => $pass]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null; 
    }

 
}
