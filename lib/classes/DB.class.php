<?php
class DB extends PDO {
	
	private static $instance;
	
	public function __construct() {
		global $config;
		
		$dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'];
		parent::__construct($dsn, $config['db']['user'], $config['db']['pass']);
	}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new DB();
        }

        return self::$instance;
    }
	
}