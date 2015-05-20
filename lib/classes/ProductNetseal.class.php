<?php
class ProductNetseal extends Product {
	
	private $serials;
	
	public function __construct() {
		parent::__construct();
		
		$this->serials = array();
	}

    public function create() {
        parent::create();

		foreach ($this->serials as $serial) {
			$q = DB::getInstance()->prepare('INSERT into products_netseals (seal_index, product_id, download, time, points, type, track, api) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
		
			$q->execute(array(array_search($serial, $this->serials), $this->id, $serial[0], $serial[1], $serial[2], $serial[3], $serial[4], $serial[5]));
		}
	}
	
	public function read($id, $showDeleted = false) {
        if (!parent::read($id, $showDeleted)) {
            return false;
        }
		
		$q = DB::getInstance()->prepare('SELECT download, time, points, type, track, api FROM products_netseals WHERE product_id = ?');
		$q->execute(array($this->id));
		$q = $q->fetchAll();
		
		if (count($q) == 0) {
			return false;
		}
		
		foreach ($q as $serial) {
			$this->serials[] = array($serial['download'], $serial['time'],  $serial['points'],  $serial['type'],  $serial['track'],  $serial['api']);
		}
		
		return true;
	}
	
	public function update() {
		parent::update();
		
		$q = DB::getInstance()->prepare('SELECT count(id) as `num` FROM products_netseals WHERE product_id = ?');
		$q->execute(array($this->id));
		$q = $q->fetchAll();
		
		if (count($q) == 0) {
			return false;
		}
		
		$serials = $q[0]['num'];
		
		if ($serials > count($this->serials)) {
			$q = DB::getInstance()->prepare('DELETE FROM products_netseals WHERE product_id = ? AND seal_index >= ?');
			$q->execute(array($this->id, count($this->serials)));
		}
		
		foreach ($this->serials as $serial) {
			$index = array_search($serial, $this->serials);
			
			if ($index + 1 <= $serials) {
				$q = DB::getInstance()->prepare('UPDATE products_netseals SET product_id = ?, download = ?, time = ?, points = ?, type = ?, track = ?, api = ? WHERE seal_index = ? AND product_id = ?');
			
				$q->execute(array($this->id, $serial[0], $serial[1], $serial[2], $serial[3], $serial[4], $serial[5], $index, $this->id));
			} else {
				$q = DB::getInstance()->prepare('INSERT into products_netseals (seal_index, product_id, download, time, points, type, track, api) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
			
				$q->execute(array($index, $this->id, $serial[0], $serial[1], $serial[2], $serial[3], $serial[4], $serial[5]));
			}
		}
		
		return true;
	}

    public function setSerials($serials) {
        $this->serials = $serials;
    }

    public function getSerials() {
        return $this->serials;
    }

}