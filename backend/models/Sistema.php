<?php

class Sistema {
    private $db;
    
    function __construct($db){
        $this->db = $db;
    }

    public function obtener_parametros_formulario() {
        return [
            'periodos' => $this->db->query('SELECT nombre, id FROM PERIODO_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),

            'secciones' => $this->db->query('SELECT * FROM SECCION')->fetchAll(PDO::FETCH_ASSOC),
            'grados'  => $this->db->query('SELECT * FROM GRADO')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO')->fetchAll(PDO::FETCH_ASSOC),

            'grados_secciones' => $this->db->query('
                SELECT gs.id, gs.grado_id, gs.seccion_id, na.id AS nivel_academico_id FROM GRADO_SECCION gs
                JOIN GRADO g ON gs.grado_id = g.id
                JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
                ORDER BY gs.id ASC
            ')->fetchAll(PDO::FETCH_ASSOC),
            'paises'  => $this->db->query('SELECT * FROM PAIS')->fetchAll(PDO::FETCH_ASSOC),
            'estados'  => $this->db->query('SELECT * FROM ESTADO')->fetchAll(PDO::FETCH_ASSOC),
            'municipios' => $this->db->query('SELECT * FROM MUNICIPIO')->fetchAll(PDO::FETCH_ASSOC),
            'parroquias' => $this->db->query('SELECT * FROM PARROQUIA')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function obtener_grados_secciones() {
        return [
            'secciones' => $this->db->query('SELECT * FROM SECCION ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC),
            'grados'  => $this->db->query('SELECT * FROM GRADO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'grados_secciones' => $this->db->query('
                SELECT gs.id, gs.grado_id, gs.seccion_id, gs.estado, na.id AS nivel_academico_id FROM GRADO_SECCION gs
                JOIN GRADO g ON gs.grado_id = g.id
                JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
                ORDER BY gs.id DESC
            ')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }


    public function obtener_niveles_academicos() {
        return $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtener_grados() {
        return [
            'grados'  => $this->db->query('SELECT * FROM GRADO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
    
    public function obtener_secciones() {
        return  $this->db->query('SELECT * FROM SECCION ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }


    public function crear_primer_admin($nombre, $pass) {
        $tabla_llena = ($this->db->query("SELECT 1 FROM USUARIO LIMIT 1")->fetch());

        if ($tabla_llena) {
            return false; 
        }   

        $stmt = $this->db->prepare("INSERT INTO USUARIO (nombre, pass, rol) VALUES (:nombre, :pass, :rol)");

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
        $stmt = $this->db->prepare("SELECT id, nombre, rol FROM USUARIO WHERE nombre = :nombre AND pass = :pass");
        $stmt->execute(['nombre' => $nombre, 'pass' => $pass]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
?>