<?php 

    class Database 
    {
        private static $instance;
        private $pdo;
        
        function __construct()
        {
            try {
                $this->pdo = new PDO('mysql:host=localhost;dbname=db_peñalver;charset=utf8', 'root', '');
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Mysql Crash'. $e);
            }
        }

        public static function get_instance() {
            if(self::$instance == null) {
                self::$instance = new Database();
            }

            return self::$instance->pdo;
        }
    }

    // $db = Database::getInstance()

?>