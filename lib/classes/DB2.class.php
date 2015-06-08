<?php
class DB2 extends PDO {

    private static $instance;

    public function __construct() {
        global $config;

        $dsn = 'mysql:host=192.99.39.168;dbname=admin_payivy';
        parent::__construct($dsn, 'admin_payivy', '3wO11CP2Xn');
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new DB2();
        }

        return self::$instance;
    }

}