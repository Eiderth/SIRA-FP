<?php

class Sistema {
    
    private $db;
    public $type = 'Sistema';
    
    function __construct($db){
        $this->db = $db;
    }

    public function existe_registro_usuario() {
        return (int) $this->db->query('SELECT 1 FROM USUARIO LIMIT 1')->fetchColumn() === 1;
    }

    public function crear_primer_admin($nombre, $pass) {
        if (empty($nombre) || empty($pass)) return;

        $tabla_llena = (int)($this->db->query("SELECT 1 FROM USUARIO LIMIT 1")->fetchColumn()) === 1;
        if ($tabla_llena) return false; 
                  
        $pass_hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO USUARIO (nombre, pass, rol) VALUES (:nombre, :pass, :rol)");

        $resultado = $stmt->execute([
            'nombre'=> $nombre,
            'pass' => $pass_hash,
            'rol' => 'Administrador'
        ]);

        if(empty($resultado)) return false;

        $id = $this->db->lastInsertId();
        $this->crear_cookie($id);

        return  $id;
    }

    public function buscar_usuario($nombre, $pass) {
        if (empty($nombre) || empty($pass)) return;

        $stmt = $this->db->prepare(
            "SELECT id, nombre, rol, pass FROM USUARIO WHERE nombre = :nombre AND estado = 'Activo'"
        );

        $stmt->execute(['nombre' => $nombre]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($usuario)) return null;
        if(!password_verify($pass, $usuario['pass'])) return null; 

        $this->crear_cookie($usuario['id']);
        
        unset($usuario['pass']);
        
        return $usuario;        
    }

    private function crear_cookie($id) {
        if (empty($id)) return;

        $token = bin2hex(random_bytes(32));
        $tiempo_expiracion = time() + 8 * 3600;
        
        $stmt = $this->db->prepare('UPDATE USUARIO SET session_token = :token WHERE id = :id');    
        $stmt->execute(['token' => $token, 'id' => $id]);
        
        setcookie(
            'session_token', 
            $token,
            [
                'expires' =>  $tiempo_expiracion,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function validar_sesion() {
        if (empty($_COOKIE['session_token'])) return false;

        $token = $_COOKIE['session_token'];

        $stmt = $this->db->prepare(
            "SELECT id, nombre, rol FROM USUARIO WHERE session_token = :token AND estado = 'Activo' LIMIT 1 "
        );
        
        $stmt->execute(['token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ? $usuario : false;
  
    }

    public function cerrar_sesion() {
        if (!empty($_COOKIE['session_token'])) {
            $token = $_COOKIE['session_token'];
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE USUARIO SET session_token = NULL WHERE session_token = :token");
            $stmt->execute(['token' => $token]);
            $this->db->commit();

            setcookie('session_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

}
?>