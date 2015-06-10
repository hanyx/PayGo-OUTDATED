<?php
class Affiliate {

    private $id;
	private $deleted;
    private $email;
	private $password;
    private $productId;
    private $orders;
    private $unpaidOrders;
    private $unpaidFiat;

    public function __construct() {
        $this->id = 0;
        $this->deleted = false;
        $this->email = '';
        $this->password = '';
        $this->productId = 0;
        $this->orders = 0;
        $this->unpaidOrders = 0;
        $this->unpaidFiat = 0;
    }

	public function create() {
		$q = DB::getInstance()->prepare('INSERT into affiliates (email, password, product_id) VALUES (?, ?, ?)');
		
		$q->execute(array($this->email, $this->password, $this->productId));

        $this->readByEmailProductId($this->email, $this->productId);
	}
	
	public function read($id, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id, deleted, email, password, product_id, orders, unpaid_orders, unpaid_fiat FROM affiliates WHERE id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($id, $showDeleted, false));
		$q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

		$this->id = $q[0]['id'];
		$this->deleted = $q[0]['deleted'];
		$this->email = $q[0]['email'];
		$this->password = $q[0]['password'];
		$this->productId = $q[0]['product_id'];
		$this->orders = $q[0]['orders'];
		$this->unpaidOrders = $q[0]['unpaid_orders'];
		$this->unpaidFiat = $q[0]['unpaid_fiat'];
		
		return true;
	}
	
	public function readByEmail($email, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id FROM affiliates WHERE email = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($email, $showDeleted, false));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id'], $showDeleted);
	}

    public function readByEmailProductId($email, $productId, $showDeleted = false) {
        $q = DB::getInstance()->prepare('SELECT id FROM affiliates WHERE email = ? AND product_id = ? AND (deleted = ? OR deleted = ?)');
        $q->execute(array($email, $productId, $showDeleted, false));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id'], $showDeleted);
    }
		
	public function update() {
		$q = DB::getInstance()->prepare('UPDATE affiliates SET deleted = ?, orders = ?, unpaid_orders = ?, unpaid_fiat = ? WHERE id = ?');
		$q->execute(array($this->deleted, $this->orders, $this->unpaidOrders, $this->unpaidFiat, $this->id));
	}

	public function pay() {
		$paidOrders = $this->unpaidOrders;
		$paidAmount = $this->unpaidFiat;
		
		$this->unpaidOrders = 0;
		$this->unpaidFiat = 0;
		
		$this->update();
		
		$mailer = new Mailer();

		$user = new User();

        $product = $this->getProduct(true);

		$user->read($product->getSellerId(), true);
		
		$mailer->sendTemplate(EmailTemplate::AFFILIATEPAID, $this->email, $user->getUsername(), $product->getTitle(), $paidOrders, $paidAmount);
	}
	
	public function getProduct($showDeleted = false) {
		return Product::getProduct($this->productId, $showDeleted);
	}
	
	public static function getAffiliatesByUser($uid, $showDeleted = false) {
		$affiliates = array();
		
		$q = DB::getInstance()->prepare('SELECT a.id FROM affiliates AS a JOIN products AS p ON (p.id = a.product_id) WHERE p.seller_id = ? AND (a.deleted = ? OR a.deleted = ?)');
		$q->execute(array($uid, $showDeleted, false));
		$q = $q->fetchAll();

		foreach ($q as $p) {
			$affiliate = new Affiliate();
			if ($affiliate->read($p['id'], $showDeleted)) {
				$affiliates[] = $affiliate;
			}
		}
		
		return $affiliates;
	}
	
	public static function getAffiliatesByProduct($pid, $showDeleted = false) {
		$affiliates = array();
		
		$q = DB::getInstance()->prepare('SELECT id FROM affiliates WHERE product_id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($pid, $showDeleted, false));
		$q = $q->fetchAll();

        foreach ($q as $p) {
            $affiliate = new Affiliate();
            if ($affiliate->read($p['id'], $showDeleted)) {
                $affiliates[] = $affiliate;
            }
        }
		
		return $affiliates;
	}

    public function getUnpaidOrders() {
        return $this->unpaidOrders;
    }

    public function getUnpaidFiat() {
        return $this->unpaidFiat;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getOrders() {
        return $this->orders;
    }

    public function getId() {
        return $this->id;
    }

    public function getProductId() {
        return $this->productId;
    }

    public function setUnpaidOrders($unpaidOrders) {
        $this->unpaidOrders = $unpaidOrders;
    }

    public function setUnpaidFiat($unpaidFiat) {
        $this->unpaidFiat = $unpaidFiat;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = password_hash($password, 1);
    }

    public function setProductId($productId) {
        $this->productId = $productId;
    }

}