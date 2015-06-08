<?php
class Product {
	
	protected $id;
    private $url;
	private $deleted;
	private $sellerId;
	private $title;
	private $description;
	private $price;
	private $type;
	private $currency;
	private $visible;
    private $customDelivery;
    private $paypalSubLength;
    private $paypalSubUnit;
    private $requireShipping;
    private $questions;
	
	private $affiliateEnabled;
	private $affiliatePercent;
	private $affiliateSecondaryLink;
	private $affiliateId;

    private $successUrl;

    private $urlTitle;
	
	public function __construct() {
        $this->id = 0;
        $this->url = '';
        $this->deleted = false;
        $this->sellerId = 0;
        $this->title = '';
        $this->description = '';
        $this->price = 0;
        $this->type = -1;
        $this->currency = array();
        $this->visible = false;
        $this->customDelivery = '';
        $this->paypalSubLength = 0;
        $this->paypalSubUnit = -1;
        $this->requireShipping = 0;
        $this->questions = array();

        $this->affiliateEnabled = false;
        $this->affiliatePercent = 0;
        $this->affiliateSecondaryLink = '';
        $this->affiliateId = '';
        $this->successUrl = '';

        $this->urlTitle = '';
	}
	
	public function create() {
		while (true) {
			$this->affiliateId = generateRandomString(20);
			
			$product = new Product();
			
			if (!$product->readByAffiliateId($this->affiliateId, true)) {
				break;
			}
		}

        while (true) {
            $this->url = generateRandomString(20);

            $product = new Product();

            if (!$product->readByUrl($this->url, true)) {
                break;
            }
        }
		
		$q = DB::getInstance()->prepare('INSERT into products (url, seller_id, title, description, price, type, currency, visible, custom_delivery, pp_sub_length, pp_sub_unit, require_shipping, affiliate_enabled, affiliate_percent, affiliate_secondary_link, affiliate_id, after_success_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		
		$q->execute(array($this->url, $this->sellerId, $this->title, $this->description, $this->price, $this->type, implode(',', $this->currency), $this->visible, $this->customDelivery, $this->paypalSubLength, $this->paypalSubUnit, $this->requireShipping, $this->affiliateEnabled, $this->affiliatePercent, $this->affiliateSecondaryLink, $this->affiliateId, $this->successUrl));

        $this->readByUrl($this->url);

        foreach ($this->questions as $question) {
            $q = DB::getInstance()->prepare('INSERT into product_questions (product_id, question_index, question) VALUES (?, ?, ?)');

            $q->execute(array($this->id, array_search($question, $this->questions), $question));
        }
    }
	
	public function read($id, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id, url, deleted, seller_id, title, description, price, type, currency, visible, custom_delivery, pp_sub_length, pp_sub_unit, require_shipping, affiliate_enabled, affiliate_percent, affiliate_secondary_link, affiliate_id, after_success_url, url_title FROM products WHERE id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($id, $showDeleted, false));
		$q = $q->fetchAll();

		if (count($q) != 1) {
			return false;
		}
		
		$this->id = $q[0]['id'];
        $this->url = $q[0]['url'];
		$this->deleted = $q[0]['deleted'];
		$this->sellerId = $q[0]['seller_id'];
		$this->title = $q[0]['title'];
		$this->description = $q[0]['description'];
		$this->price = $q[0]['price'];
		$this->type = $q[0]['type'];
		$this->currency = explode(',', $q[0]['currency']);
		$this->visible = $q[0]['visible'];
        $this->customDelivery = $q[0]['custom_delivery'];
        $this->paypalSubLength = $q[0]['pp_sub_length'];
        $this->paypalSubUnit = $q[0]['pp_sub_unit'];
        $this->requireShipping = $q[0]['require_shipping'];
		
		$this->affiliateEnabled = $q[0]['affiliate_enabled'];
		$this->affiliatePercent = $q[0]['affiliate_percent'];
		$this->affiliateSecondaryLink = $q[0]['affiliate_secondary_link'];
		$this->affiliateId = $q[0]['affiliate_id'];
        $this->successUrl = $q[0]['after_success_url'];

        $this->urlTitle = $q[0]['url_title'];

        $q = DB::getInstance()->prepare('SELECT question FROM product_questions WHERE product_id = ?');
        $q->execute(array($this->id));
        $q = $q->fetchAll();

        foreach ($q as $question) {
            $this->questions[] = $question['question'];
        }

		return true;
	}
	
	public function readByTitle($title, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id FROM products WHERE title = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($title, $showDeleted, false));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id']);
	}
	
	public function readByAffiliateId($affiliateId, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id FROM products WHERE affiliate_id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($affiliateId, $showDeleted, false));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
            return false;
        }
		
		return $this->read($q[0]['id']);
	}

    public function readByUrl($url, $showDeleted = false) {
        $q = DB::getInstance()->prepare('SELECT id FROM products WHERE url = ? AND (deleted = ? OR deleted = ?)');
        $q->execute(array($url, $showDeleted, false));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }

    public function readByUrlTitle($urlTitle, $showDeleted = false) {
        $q = DB::getInstance()->prepare('SELECT id FROM products WHERE url_title = ? AND (deleted = ? OR deleted = ?)');
        $q->execute(array($urlTitle, $showDeleted, false));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }
	
	public function update() {
		$q = DB::getInstance()->prepare('UPDATE products SET deleted = ?, title = ?, description = ?, price = ?, currency = ?, visible = ?, custom_delivery = ?, pp_sub_length = ?, pp_sub_unit = ?, require_shipping = ?, affiliate_enabled = ?, affiliate_percent = ?, affiliate_secondary_link = ?, after_success_url = ? WHERE id = ?');
        $q->execute(array($this->deleted, $this->title, $this->description, $this->price, implode(',', $this->currency), $this->visible, $this->customDelivery, $this->paypalSubLength, $this->paypalSubUnit, $this->requireShipping, $this->affiliateEnabled, $this->affiliatePercent, $this->affiliateSecondaryLink, $this->successUrl, $this->id));

        $q = DB::getInstance()->prepare('SELECT count(id) as `num` FROM product_questions WHERE product_id = ?');
        $q->execute(array($this->id));
        $q = $q->fetchAll();

        $questions = $q[0]['num'];

        if ($questions > count($this->questions)) {
            $q = DB::getInstance()->prepare('DELETE FROM product_questions WHERE product_id = ? AND question_index >= ?');
            $q->execute(array($this->id, count($this->questions)));
        }

        foreach ($this->questions as $question) {
            $index = array_search($question, $this->questions);

            if ($index + 1 <= $questions) {
                $q = DB::getInstance()->prepare('UPDATE product_questions SET question = ? WHERE question_index = ? AND product_id = ?');

                $q->execute(array($question, $index, $this->id));
            } else {
                $q = DB::getInstance()->prepare('INSERT into product_questions (question_index, product_id, question) VALUES (?, ?, ?)');

                $q->execute(array($index, $this->id, $question));
            }
        }
    }

	public function getOrders($completedOnly = true) {
		return Order::getOrdersByProduct($this->id, $completedOnly);
	}
	
	public function getAffiliates($showDeleted = false) {
		return Affiliate::getAffiliatesByProduct($this->id, $showDeleted);
	}
	
	public function setDeleted() {
		$this->deleted = true;
	}
	
	public function isDeleted() {
		return $this->deleted;
	}
	
	public function getUrl() {
		return '/v/' . $this->url;
	}
	
	public function getAffiliateLink() {
		return $this->getUrl() . '/a/' . $this->affiliateId;
	}
	
	public function acceptsCurrency($currency) {
		return (in_array($currency, $this->currency));
	}
	
	public static function getProduct($id, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id, type FROM products WHERE id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($id, $showDeleted, false));
		$q = $q->fetchAll();

        if (count($q) != 1) {
			return false;
		}
		
		switch ($q[0]['type']) {
			case ProductType::DOWNLOAD:
				$product = new ProductDownload();
				break;
			case ProductType::SERIAL:
				$product = new ProductSerial();
				break;
			case ProductType::NETSEAL:
				$product = new ProductNetseal();
				break;
		}

        if (!$product->read($q[0]['id'], $showDeleted)) {
            return false;
        }

        return $product;
	}
	
	public static function getProductsByUser($uid, $showDeleted = false) {
		$products = array();
		
		$q = DB::getInstance()->prepare('SELECT id, type FROM products WHERE seller_id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($uid, $showDeleted, false));
		$q = $q->fetchAll();

		foreach ($q as $p) {
			$product = Product::getProduct($p['id'], $showDeleted);
			
			if ($product !== false) {
				$products[] = $product;
			}
		}
		
		return $products;
	}
	
	public function getNotes() {
		return '';
	}

    public function getSellerId() {
        return $this->sellerId;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setAffiliateEnabled($affiliateEnabled) {
        $this->affiliateEnabled = $affiliateEnabled;
    }

    public function setAffiliatePercent($affiliatePercent) {
        $this->affiliatePercent = $affiliatePercent;
    }

    public function setAffiliateSecondaryLink($affiliateSecondaryLink) {
        $this->affiliateSecondaryLink = $affiliateSecondaryLink;
    }

    public function setVisible($visible) {
        $this->visible = $visible;
    }

    public function setSellerId($sellerId) {
        $this->sellerId = $sellerId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getAffiliateEnabled() {
        return $this->affiliateEnabled;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getType() {
        return $this->type;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getAffiliatePercent() {
        return $this->affiliatePercent;
    }

    public function getVisible() {
        return $this->visible;
    }

    public function getAffiliateSecondaryLink() {
        return $this->affiliateSecondaryLink;
    }

    public function getQuestions() {
        return $this->questions;
    }

    public function setQuestions($questions) {
        $this->questions = $questions;
    }

    public function getCustomDelivery() {
        return $this->customDelivery;
    }

    public function setCustomDelivery($customDelivery) {
        $this->customDelivery = $customDelivery;
    }

    public function getPaypalSubUnit() {
        return $this->paypalSubUnit;
    }

    public function setPaypalSubUnit($paypalSubUnit) {
        $this->paypalSubUnit = $paypalSubUnit;
    }

    public function getPaypalSubLength() {
        return $this->paypalSubLength;
    }

    public function setPaypalSubLength($paypalSubLength) {
        $this->paypalSubLength = $paypalSubLength;
    }

    public function getRequireShipping() {
        return $this->requireShipping;
    }

    public function setRequireShipping($requireShipping)
    {
        $this->requireShipping = $requireShipping;
    }

    public function getSuccessUrl(){
        return $this->successUrl;
    }

    public function setSuccessUrl($successUrl){
        $successUrl = strip_tags($successUrl);
        $successUrl = str_replace("'", "", $successUrl);
        $successUrl = str_replace('"', '', $successUrl);
        if(filter_var($successUrl, FILTER_VALIDATE_URL) || $successUrl == ''){
            $this->successUrl = $successUrl;
        }
    }

    public function getViews() {
        return View::getViewsByProduct($this->id);
    }

}
