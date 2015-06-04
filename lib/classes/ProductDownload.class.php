<?php
class ProductDownload extends Product {
	
	private $fileId;
	
	public function __construct() {
		parent::__construct();

        $this->fileId = '';
	}
	
	public function create() {
		parent::create();

		$q = DB::getInstance()->prepare('INSERT into products_files (product_id, file_id) VALUES (?, ?)');
		
		$q->execute(array($this->id, $this->fileId));
	}
	
	public function read($id, $showDeleted = false) {
		if (!parent::read($id, $showDeleted)) {
			return false;
		}
		
		$q = DB::getInstance()->prepare('SELECT file_id FROM products_files WHERE product_id = ?');
		$q->execute(array($this->id));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}
		
		$this->fileId = $q[0]['file_id'];
		
		return true;
	}
	
	public function update() {
		parent::update();
		
		$q = DB::getInstance()->prepare('UPDATE products_files SET file_id = ? WHERE product_id = ?');
		$q->execute(array($this->fileId, $this->id));
	}

    public function setFileId($fileId) {
        $this->fileId = $fileId;
    }

    public function getFileId() {
        return $this->fileId;
    }

}