<?php
class ProductSerial extends Product {
	
	private $serials;
	
	public function __construct() {
		parent::__construct();
		
		$this->serials = array();
	}
	
	public function create() {
		parent::create();

		$q = DB::getInstance()->prepare('INSERT into products_serials (product_id, serials) VALUES (?, ?)');

		$q->execute(array($this->id, implode(',', $this->serials)));
	}

	public function read($id, $showDeleted = false) {
		if (!parent::read($id, $showDeleted)) {
			return false;
		}

		$q = DB::getInstance()->prepare('SELECT serials FROM products_serials WHERE product_id = ?');
		$q->execute(array($this->id));
		$q = $q->fetchAll();

		if (count($q) != 1) {
            return false;
		}

		$this->serials = explode(',', $q[0]['serials']);

        if (count($this->serials) == 1 && $this->serials[0] == '') {
            $this->serials = array();
        }

		return true;
	}

	public function update() {
		parent::update();

		$q = DB::getInstance()->prepare('UPDATE products_serials SET serials = ? WHERE product_id = ?');
		$q->execute(array(implode(',', $this->serials), $this->id));
	}

    public function setSerials($serials) {
        $this->serials = $serials;
    }

    public function getNotes() {
        return 'You have ' . count($this->serials) . ' ' . formatS(count($this->serials), 'serial') . ' left';
    }

    public function getSerials() {
        return $this->serials;
    }

    public function makeSerialString(){
        $cnt = count($this->serials);
        return $cnt >= 1000 ? '' : $cnt . ' left';
    }
}