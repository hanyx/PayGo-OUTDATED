<?php
class User {
	
	private $id;
    private $session;
	private $username;
	private $password;
	private $email;

	private $active;
	private $accountType;

	private $lastLoginTimestamp;
	private $lastLoginIp;
		
	private $paypal;
	private $bitcoin;
	private $litecoin;
	private $omnicoin;

    private $bigSizeBar;
	
	public function __construct() {
        $this->id = 0;
        $this->username = '';
        $this->password = '';
        $this->email = '';

        $this->active = false;
        $this->accountType = 0;

        $this->lastLoginTimestamp = '';
        $this->lastLoginIp = '';

        $this->paypal = '';
        $this->bitcoin = '';
        $this->litecoin = '';
        $this->omnicoin = '';
        $this->bigSizeBar = 1;
	}
	
	public function create() {
		$q = DB::getInstance()->prepare('INSERT into users (username, password, email) VALUES (?, ?, ?)');
		
		$q->execute(array($this->username, $this->password, $this->email));

        $this->readByEmail($this->email);
	}
	
	public function read($id) {
		$q = DB::getInstance()->prepare('SELECT id, session, username, password, email, active, account_type, last_login_timestamp, last_login_ip, paypal, bitcoin, litecoin, omnicoin, big_sidebar FROM users WHERE id = ?');
		$q->execute(array($id));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}

        $this->id = $q[0]['id'];
        $this->session = $q[0]['session'];
		$this->username = $q[0]['username'];
		$this->password = $q[0]['password'];
		$this->email = $q[0]['email'];

		$this->active = $q[0]['active'];
		$this->accountType = $q[0]['account_type'];

		$this->lastLoginTimestamp = $q[0]['last_login_timestamp'];
		$this->lastLoginIp = $q[0]['last_login_ip'];

		$this->paypal = $q[0]['paypal'];
		$this->bitcoin = $q[0]['bitcoin'];
		$this->litecoin = $q[0]['litecoin'];
		$this->omnicoin = $q[0]['omnicoin'];

        $this->bigSizeBar = $q[0]['big_sidebar'];

		return true;
	}
	
	public function readByUsername($username) {
        $q = DB::getInstance()->prepare('SELECT id FROM users WHERE username = ?');
        $q->execute(array($username));
		$q = $q->fetchAll();

		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id']);
	}

    public function readByEmail($email) {
        $q = DB::getInstance()->prepare('SELECT id FROM users WHERE email = ?');
        $q->execute(array($email));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }
	
	public function update() {
		$q = DB::getInstance()->prepare('UPDATE users SET session = ?, username = ?, password = ?, email = ?, active = ?, account_type = ?, last_login_timestamp = ?, last_login_ip = ?, paypal = ?, bitcoin = ?, litecoin = ?, omnicoin = ?, big_sidebar = ? WHERE id = ?');
		$q->execute(array($this->session, $this->username, $this->password, $this->email, $this->active, $this->accountType, $this->lastLoginTimestamp, $this->lastLoginIp, $this->paypal, $this->bitcoin, $this->litecoin, $this->omnicoin, $this->bigSizeBar, $this->id));
	}

    public function getViews() {
        return View::getViewsByUser($this->id);
    }
		
	public function checkPassword($password) {
		return password_verify($password, $this->password);
	}
	
	public function setPassword($password, $hash = true) {
		$this->password = $hash ? password_hash($password, 1) : $password;
	}
	
	public function setLastLogin($ip) {
		$this->lastLoginTimestamp = date('Y-m-d H:i:s') . ',' . explode(',', $this->lastLoginTimestamp)[0];
		$this->lastLoginIp = $ip . ',' . explode(',', $this->lastLoginIp)[0];
	}
	
	public function getLastLoginIp() {
		$ips = explode(',', $this->lastLoginIp);
		return (count($ips) == 2) ? $ips[1] : '';
	}
	
	public function getLastLoginTimestamp() {
		$timestamps = explode(',', $this->lastLoginTimestamp);
		return (count($timestamps) == 2) ? $timestamps[1] : '';
	}
	
	public function getProducts($showDeleted = false) {
		return Product::getProductsByUser($this->id, $showDeleted);
	}

    public function getCoupons(){
        return Coupon::getCouponsByUser($this->id);
    }
	
	public function getMessages($folder = MessageFolder::INBOX, $showDeleted = false) {
		return Message::getMessagesByUser($this->id, $folder, $showDeleted);
	}
	
	public function getAffiliates($showDeleted = false) {
		return Affiliate::getAffiliatesByUser($this->id, $showDeleted);
	}

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setPaypal($paypal) {
        $this->paypal = $paypal;
    }

    public function setBitcoin($bitcoin) {
        $this->bitcoin = $bitcoin;
    }

    public function setLitecoin($litecoin) {
        $this->litecoin = $litecoin;
    }

    public function setOmnicoin($omnicoin) {
        $this->omnicoin = $omnicoin;
    }

    public function getActive() {
        return $this->active;
    }

    public function getId() {
        return $this->id;
    }

    public function getAccountType() {
        return $this->accountType;
    }

    public function setActive() {
        $this->active = true;
    }

    public function getSession() {
        return $this->session;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPaypal() {
        return $this->paypal;
    }

    public function getBitcoin() {
        return $this->bitcoin;
    }

    public function getLitecoin() {
        return $this->litecoin;
    }

    public function getOmnicoin() {
        return $this->omnicoin;
    }

    public function getFiles($showDeleted = false) {
        return File::getFilesByUser($this->id, $showDeleted);
    }

    public function getBigSizeBar(){
        return $this->bigSizeBar;
    }

    public function setBigSizeBar($bigSizeBar){
        $this->bigSizeBar = $bigSizeBar;
    }
}