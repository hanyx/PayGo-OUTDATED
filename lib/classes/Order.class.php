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
    private $successUrl;
    private $merchant;

    private $couponId;
    private $couponUsed;
    private $couponName;
    private $couponReduction;

    private $affiliate;
    private $affiliateUsed;

    private $productDelivery;

    private $qr_url;
    private $crypto_to;

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
        $this->successUrl = '';
        $this->merchant = '';

        $this->couponUsed = false;
        $this->couponName = '';
        $this->couponReduction = 0;

        $this->affiliate = 0;
        $this->affiliateUsed = false;

        $this->productDelivery = 0;
        $this->couponId = 0;
        $this->qr_url = '';
        $this->crypto_to = '';
	}
	
	public function create() {
		while (true) {
			$this->txid = 'PI-' . generateRandomString(61);
			
			$order = new Order();
            
			if (!$order->readByTxid($this->txid, false)) {
				break;
			}
		}
		
		$this->date = date('Y-m-d H:i:s');
		
		$q = DB::getInstance()->prepare('INSERT into orders (txid, date, product_id, quantity, currency, fiat, email, ip, after_success_url, merchant, coupon_used, coupon_name, coupon_reduction, affiliate, affiliate_used, product_delivery, coupon_id, qr_url, crypto_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		
		$q->execute(array($this->txid, $this->date, $this->productId, $this->quantity, $this->currency, $this->fiat, $this->email, $this->ip, $this->successUrl, $this->merchant, $this->couponUsed, $this->couponName, $this->couponReduction, $this->affiliate, $this->affiliateUsed, $this->productDelivery, $this->couponId, $this->qr_url, $this->crypto_to));

        foreach ($this->questions as $question) {
            $q = DB::getInstance()->prepare('INSERT into order_questions (order_id, question_index, question, response) VALUES (?, ?, ?, ?)');

            $q->execute(array($this->id, array_search($question, $this->questions), $question[0], $question[1]));
        }

        $this->readByTxid($this->txid, false);
	}
	
	public function read($id, $completedOnly = true) {
		$q = DB::getInstance()->prepare('SELECT * FROM orders WHERE id = ? AND (completed = ? OR completed = ?)');
		$q->execute(array($id, $completedOnly, true));
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
        $this->successUrl = $q[0]['after_success_url'];
        $this->merchant = $q[0]['merchant'];

        $this->couponId = $q[0]['coupon_id'];
        $this->couponUsed = $q[0]['coupon_used'];
        $this->couponName = $q[0]['coupon_name'];
        $this->couponReduction = $q[0]['coupon_reduction'];

        $this->affiliate = $q[0]['affiliate'];
        $this->affiliateUsed = $q[0]['affiliate_used'];

        $this->productDelivery = $q[0]['product_delivery'];
        $this->qr_url = $q[0]['qr_url'];
        $this->crypto_to = $q[0]['crypto_to'];

        $q = DB::getInstance()->prepare('SELECT question, response FROM order_questions WHERE order_id = ? AND (completed = ? OR completed = ?)');
        $q->execute(array($id, $completedOnly, true));
        $q = $q->fetchAll();

        foreach ($q as $question) {
            $this->questions[] = array($question['question'], $question['response']);
        }

		return true;
	}
	
	public function readByTxid($txid, $completedOnly = true) {
		$q = DB::getInstance()->prepare('SELECT id FROM orders WHERE txid = ? AND (completed = ? OR completed = ?)');
		$q->execute(array($txid, $completedOnly, true));
		$q = $q->fetchAll();

		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id'], $completedOnly);
	}

    public function update() {
        $q = DB::getInstance()->prepare('UPDATE orders SET merchant = ?, completed = ?, processor_txid = ?, native = ?, product_delivery = ?, qr_url = ?, crypto_to = ? WHERE id = ?');
        $q->execute(array($this->merchant, $this->completed, $this->processorTxid, $this->native, $this->productDelivery, $this->qr_url, $this->crypto_to, $this->id));
    }
	
	public static function getOrdersByProduct($pid, $completedOnly = true) {
		$orders = array();
		
		$q = DB::getInstance()->prepare('SELECT id FROM orders WHERE product_id = ? AND (completed = ? OR completed = ?)');
		$q->execute(array($pid, $completedOnly, true));
		$q = $q->fetchAll();

        foreach ($q as $p) {
            $order = new Order();
            if ($order->read($p['id'], $completedOnly)) {
                $orders[] = $order;
            }
        }

		return $orders;
	}

    public static function getOrdersByCoupon($cid, $completedOnly = true){
        $orders = array();

        $q = DB::getInstance()->prepare("SELECT id FROM orders WHERE coupon_id = ? AND (completed = ? OR completed = ?)");
        $q->execute(array($cid, $completedOnly, true));
        $q = $q->fetchAll();

        foreach($q as $c){
            $order = new Order();
            if($order->read($c['id'], $completedOnly)) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    public function process() {
        $product = new Product();

        if ($product->read($this->productId)) {
            $product = Product::getProduct($product->getId());

            switch ($product->getType()) {
                case ProductType::DOWNLOAD:
                    $download = new Download();

                    $download->setFileId($product->getFileId());

                    $download->create();

                    $mailer = new Mailer();

                    $mailer->sendTemplate(EmailTemplate::DOWNLOAD, $this->email, '', $download->getLink(), $product->getCustomDelivery(), $product->getSellerId(), $this->txid);

                    break;
                case ProductType::NETSEAL:
                    $netseal = new Netseal();

                    $seals = array();

                    foreach ($product->getSerials() as $seal) {
                        $seals[] = array($seal[0], $netseal->createCode($seal[1], $seal[2], $seal[3], $seal[4], $seal[5]));
                    }

                    $mailer = new Mailer();

                    $mailer->sendTemplate(EmailTemplate::NETSEALS, $this->email, '', $seals, $product->getCustomDelivery(), $product->getSellerId(), $this->txid);

                    break;
                case ProductType::SERIAL:
                    if (count($product->getSerials()) < $this->getQuantity()) {
                        $mailer = new Mailer();

                        $mailer->sendTemplate(EmailTemplate::OUTOFSTOCK, $this->email, '', '', '', $product->getSellerId(), $this->txid);
                    } else {

                        $keys = array_slice($product->getSerials(), 0, $this->quantity);

                        $product->setSerials(array_slice($product->getSerials(), $this->quantity));

                        $product->update();

                        $mailer = new Mailer();

                        $mailer->sendTemplate(EmailTemplate::SERIALS, $this->email, '', $keys, $product->getCustomDelivery(), $product->getSellerId(), $this->txid);

                    }

                    break;
            }
        }

        if ($this->affiliateUsed) {
            $affiliate = new Affiliate();

            $affiliate->read($this->affiliate);

            $affiliate->setUnpaidFiat($affiliate->getUnpaidFiat() + ($this->calculateFiatWithCoupon() * $this->getQuantity() * $product->getAffiliatePercent()));

            $affiliate->setUnpaidOrders($affiliate->getUnpaidOrders() + 1);
        }

        $user = new User();

        if ($user->read($product->getSellerId())) {

            $mailer = new Mailer();

            $mailer->sendTemplate(EmailTemplate::SELLERSALE, $user->getEmail(), $user->getEmail(), $product->getTitle(), $this->calculateFiatWithCoupon(), $this->quantity, $this->calculateFiatWithCoupon() * $this->quantity, $this->email, $this->txid, $this->questions);

        }
    }

    public static function getOrdersByUser($uid, $completedOnly = true) {
        $orders = array();

        $q = DB::getInstance()->prepare('SELECT o.id FROM orders AS `o` JOIN products AS `p` ON (p.id = o.product_id) WHERE p.seller_id = ? AND (o.completed = ? OR o.completed = ?)');
        $q->execute(array($uid, $completedOnly, true));
        $q = $q->fetchAll();

        foreach ($q as $p) {
            $order = new Order();
            if ($order->read($p['id'], $completedOnly)) {
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

    public function setCompleted($completed) {
        $this->completed = $completed;
    }

    public function isCouponUsed() {
        return $this->couponUsed;
    }

    public function setCouponUsed($couponUsed) {
        $this->couponUsed = $couponUsed;
    }

    public function getCouponName() {
        return $this->couponName;
    }

    public function setCouponName($couponName) {
        $this->couponName = $couponName;
    }

    public function getCouponReduction() {
        return $this->couponReduction;
    }

    public function setCouponReduction($couponReduction) {
        $this->couponReduction = $couponReduction;
    }

    public function calculateFiatWithCoupon() {
        return $this->couponUsed ? round($this->fiat * ((100 - $this->couponReduction) / 100), 2) : $this->fiat;
    }

    public function getId() {
        return $this->id;
    }

    public function isAffiliateUsed() {
        return $this->affiliateUsed;
    }

    public function setAffiliateUsed($affiliateUsed) {
        $this->affiliateUsed = $affiliateUsed;
    }

    public function getAffiliate() {
        return $this->affiliate;
    }

    public function setAffiliate($affiliate) {
        $this->affiliate = $affiliate;
    }

    public function getProductDelivery() {
        return $this->productDelivery;
    }

    public function setProductDelivery($productDelivery) {
        $this->productDelivery = $productDelivery;
    }

    public function getCouponId()
    {
        return $this->couponId;
    }

    public function setCouponId($couponId)
    {
        $this->couponId = $couponId;
    }

    public function getQrUrl()
    {
        return $this->qr_url;
    }

    public function setQrUrl($qr_url)
    {
        $this->qr_url = $qr_url;
    }

    public function getCryptoTo()
    {
        return $this->crypto_to;
    }

    public function setCryptoTo($crypto_to)
    {
        $this->crypto_to = $crypto_to;
    }
}