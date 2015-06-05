<?php
class Order {
	
	private $id;
	private $completed;
	private $txid;
	private $processorTxid;
	private $date;
	private $productId;
    private $quantity;
	
	private $currency;
	private $fiat;
	private $native;
	
	private $email;
	private $ip;
	private $questions;
    private $coupon;
    private $successUrl;
    private $merchant;
	
	public function __construct() {
        $this->id = 0;
        $this->completed = false;
        $this->txid = '';
        $this->processorTxid = '';
        $this->date = '';
        $this->productId = 0;
        $this->quantity = 0;
        $this->currency = 0;
        $this->fiat = 0;
        $this->native = 0;
        $this->email = '';
        $this->ip = '';
        $this->questions = array();
        $this->coupon = "0";
        $this->successUrl = '';
        $this->merchant = '';
	}
	
	public function create() {
		while (true) {
			$this->txid = generateRandomString(64);
			
			$order = new Order();
            
			if (!$order->readByTxid($this->txid)) {
				break;
			}
		}
		
		$this->date = date('Y-m-d H:i:s');
		
		$q = DB::getInstance()->prepare('INSERT into orders (txid, date, product_id, quantity, currency, fiat, email, ip, coupon_id, after_success_url, merchant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		
		$q->execute(array($this->txid, $this->date, $this->productId, $this->quantity, $this->currency, $this->fiat, $this->email, $this->ip, $this->coupon, $this->successUrl, $this->merchant));

        $this->readByTxid($this->txid);

        foreach ($this->questions as $question) {
            $q = DB::getInstance()->prepare('INSERT into order_questions (order_id, question_index, question, response) VALUES (?, ?, ?, ?)');

            $q->execute(array($this->id, array_search($question, $this->questions), $question[0], $question[1]));
        }
	}
	
	public function read($id) {
		$q = DB::getInstance()->prepare('SELECT id, completed, txid, processor_txid, date, product_id, quantity, currency, fiat, native, email, ip, coupon_id, after_success_url, merchant FROM orders WHERE id = ?');
		$q->execute(array($id));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}
		
		$this->id = $q[0]['id'];
		$this->completed = $q[0]['completed'];
		$this->txid = $q[0]['txid'];
		$this->processorTxid = $q[0]['processor_txid'];
		$this->date = $q[0]['date'];
		$this->productId = $q[0]['product_id'];
        $this->quantity = $q[0]['quantity'];
		$this->currency = $q[0]['currency'];
		$this->fiat = $q[0]['fiat'];
		$this->native = $q[0]['native'];
		$this->email = $q[0]['email'];
		$this->ip = $q[0]['ip'];
        $this->coupon = $q[0]['coupon_id'];
        $this->successUrl = $q[0]['after_success_url'];
        $this->merchant = $q[0]['merchant'];

        $q = DB::getInstance()->prepare('SELECT question, response FROM order_questions WHERE order_id = ?');
        $q->execute(array($id));
        $q = $q->fetchAll();

        foreach ($q as $question) {
            $this->questions[] = array($question['question'], $question['response']);
        }

		return true;
	}
	
	public function readByTxid($txid) {
		$q = DB::getInstance()->prepare('SELECT id FROM orders WHERE txid = ?');
		$q->execute(array($txid));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id']);
	}

    public function update() {
        $q = DB::getInstance()->prepare('UPDATE orders SET completed = ?, processor_txid = ? WHERE id = ?');
        $q->execute(array($this->completed, $this->processorTxid, $this->id));
    }
	
	public static function getOrdersByProduct($pid, $completed = true) {
		$orders = array();
		
		$q = DB::getInstance()->prepare('SELECT id FROM orders WHERE product_id = ? AND completed = ?');
		$q->execute(array($pid, $completed));
		$q = $q->fetchAll();

        foreach ($q as $p) {
            $order = new Order();
            if ($order->read($p['id'])) {
                $orders[] = $order;
            }
        }

		return $orders;
	}

    public static function getOrdersByCoupon($cid){
        $orders = array();

        $q = DB::getInstance()->prepare("SELECT id FROM orders WHERE coupon_id = ?");
        $q->execute(array($cid));
        $q = $q->fetchAll();

        foreach($q as $c){
            $order = new Order();
            if($order->read($c['id'])){
                $orders[] = $order;
            }
        }

        return $orders;
    }

    public function process() {
        //Process order, email user, etc

        $product = new Product();

        if ($product->read($this->productId)) {
            $product = Product::getProduct($product->getId());

            switch ($product->getType()) {
                case ProductType::DOWNLOAD:
                    $download = new Download();

                    $download->setFileId($product->getFileId());

                    $download->create();

                    $mailer = new Mailer();

                    $mailer->sendTemplate(EmailTemplate::DOWNLOAD, $this->email, '', $download->getLink(), $product->getCustomDelivery());

                    break;
                case ProductType::NETSEAL:
                    $netseal = new Netseal();

                    $seals = array();

                    foreach ($product->getSerials() as $seal) {
                        $seals[] = array($seal[0], $netseal->createCode($seal[1], $seal[2], $seal[3], $seal[4], $seal[5]));
                    }

                    $mailer = new Mailer();

                    $mailer->sendTemplate(EmailTemplate::NETSEALS, $this->email, '', $seals, $product->getCustomDelivery());

                    break;
                case ProductType::SERIAL:
                    if (count($product->getSerials()) < $this->getQuantity()) {
                        $mailer = new Mailer();

                        $mailer->sendTemplate(EmailTemplate::OUTOFSTOCK, $this->email, '');
                    } else {

                        $keys = array_slice($product->getSerials(), 0, $this->quantity);

                        $product->setSerials(array_slice($product->getSerials(), $this->quantity));

                        $mailer = new Mailer();

                        $mailer->sendTemplate(EmailTemplate::SERIALS, $this->email, '', $keys, $product->getCustomDelivery());

                    }

                    break;
            }
        }
    }

    public static function getOrdersByUser($uid) {
        $orders = array();

        $q = DB::getInstance()->prepare('SELECT o.id FROM orders AS `o` JOIN products AS `p` ON (p.id = o.product_id) WHERE p.seller_id = ? AND o.completed = 0');
        $q->execute(array($uid));
        $q = $q->fetchAll();

        foreach ($q as $p) {
            $order = new Order();
            if ($order->read($p['id'])) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    public function getFiat() {
        return $this->fiat;
    }

    public function getDate() {
        return $this->date;
    }

    public function getProductId() {
        return $this->productId;
    }

    public function setProductId($productId) {
        $this->productId = $productId;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function setFiat($fiat) {
        $this->fiat = $fiat;
    }

    public function getNative() {
        return $this->native;
    }

    public function setNative($native) {
        $this->native = $native;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getCoupon(){
        return $this->coupon;
    }

    public function setCoupon($cid) {
        $this->coupon = $cid;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

    public function getQuestions() {
        return $this->questions;
    }

    public function setQuestions($questions) {
        $this->questions = $questions;
    }

    public function getTxid() {
        return $this->txid;
    }

    public function getProcessorTxid() {
        return $this->processorTxid;
    }

    public function setProcessorTxid($processorTxid) {
        $this->processorTxid = $processorTxid;
    }

    public function setSuccessUrl($successUrl){
        if(filter_var($successUrl, FILTER_VALIDATE_URL) || $successUrl == ''){
            $this->successUrl = $successUrl;
        }
    }

    public function getSuccessUrl() {
        return $this->successUrl;
    }

    public function isCompleted() {
        return $this->completed;
    }

    public function getMerchant() {
        return $this->merchant;
    }

    public function setMerchant($merchant) {
        $this->merchant = $merchant;
    }
}