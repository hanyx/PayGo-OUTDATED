<?php
class TwoFactorRequest extends ErrorSuccessMessages {
	
	private $id;
	private $token;
	private $used;
	
	private $userId;
	private $action;
	private $data;
	
	public function __construct() {
		parent::__construct();

        $this->id = 0;
        $this->token = '';
        $this->used = false;

        $this->userId = 0;
        $this->action = 0;
        $this->data = '';
	}
	
	public function create() {
        while (true) {
            $this->token = generateRandomString();

            $tfr = new TwoFactorRequest();

            if (!$tfr->readByToken($this->token)) {
                break;
            }
        }

		$q = DB::getInstance()->prepare('INSERT into 2fa (token, user_id, action, data) VALUES (?, ?, ?, ?)');
		$q->execute(array($this->token, $this->userId, $this->action, $this->data));
	}
	
	public function read($id) {
		$q = DB::getInstance()->prepare('SELECT id, token, used, user_id, action, data FROM 2fa WHERE id = ?');
		$q->execute(array($id));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}
		
		$this->id = $q[0]['id'];
		$this->token = $q[0]['token'];
		$this->used = $q[0]['used'];
		
		$this->userId = $q[0]['user_id'];
		$this->action = $q[0]['action'];
		$this->data = $q[0]['data'];
		
		return true;
	}
	
	public function readByToken($token) {
		$q = DB::getInstance()->prepare('SELECT id FROM 2fa WHERE token = ?');
		$q->execute(array($token));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}

		return $this->read($q[0]['id']);
	}
	
	public function update() {
		$q = DB::getInstance()->prepare('UPDATE 2fa SET token = ?, used = ?, user_id = ?, action = ?, data = ? WHERE id = ?');
		$q->execute(array($this->token, $this->used, $this->userId, $this->action, $this->data, $this->id));
	}
	
	public function process() {
		if ($this->used) {
			return false;
		}
		
		switch ($this->action) {
			case TwoFactorRequestAction::ACTIVATE:
				$user = new User();

				$user->read($this->userId);
				$user->setActive();
				$user->update();
				
				$this->addMessage(new ErrorSuccessMessage('Account activated. Please login', false));
				break;
			case TwoFactorRequestAction::RESET:
				break;
			case TwoFactorRequestAction::UPDATEPASSWORD:
				$user = new User();
                $user->read($this->userId);
				$user->setPassword($this->data, false);
				$user->update();
				
				$this->addMessage(new ErrorSuccessMessage('Password Updated', false));
				break;
		}
		
		$this->used = true;
		$this->update();
		return true;
	}

    public function getToken() {
        return $this->token;
    }

    public function getUsed() {
        return $this->used;
    }

    public function getAction() {
        return $this->action;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setData($data) {
        $this->data = $data;
    }

}