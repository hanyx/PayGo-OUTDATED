<?php
class Message {
	
	private $id;
	private $deleted;
	private $folder;
	private $recipient;
	private $sender;
	private $message;
	private $date;
	private $read;
	
	public function __construct() {
        $this->id = 0;
        $this->deleted = false;
        $this->folder = 0;
        $this->recipient = '';
        $this->sender = '';
        $this->message = '';
        $this->date = '';
        $this->read = false;
	}
	
	public function create() {
		$this->date = date('Y-m-d H:i:s');
		
		$q = DB::getInstance()->prepare('INSERT into messages (folder, recipient, sender, message, date) VALUES (?, ?, ?, ?, ?)');
		
		$q->execute(array($this->folder, $this->recipient, $this->sender, $this->message, $this->date));

        $this->id = DB::getInstance()->lastInsertId();
	}
	
	public function read($id, $showDeleted = false) {
		$q = DB::getInstance()->prepare('SELECT id, deleted, folder, recipient, sender, message, date, is_read FROM messages WHERE id = ? AND (deleted = ? OR deleted = ?)');
		$q->execute(array($id, $showDeleted, false));
		$q = $q->fetchAll();
		
		if (count($q) != 1) {
			return false;
		}
		
		$this->id = $q[0]['id'];
		$this->deleted = $q[0]['deleted'];
		$this->folder = $q[0]['folder'];
		$this->recipient = $q[0]['recipient'];
		$this->sender = $q[0]['sender'];
		$this->message = $q[0]['message'];
		$this->date = $q[0]['date'];
		$this->read = $q[0]['is_read'];
		
		return true;
	}
	
	public function update() {
		$q = DB::getInstance()->prepare('UPDATE messages SET deleted = ?, is_read = ? WHERE id = ?');
		$q->execute(array($this->deleted, $this->read, $this->id));
	}

	public function send() {
		if (is_numeric($this->recipient)) {
			$user = new User();

			$user->read($this->recipient);
			
			$this->folder = MessageFolder::INBOX;

            $this->create();
			
			$mailer = new Mailer();
			
			$mailer->sendTemplate(EmailTemplate::SELLERMESSAGE, $user->getEmail(), $user->getUsername(), $this->sender, $this->message);
		} else if (is_numeric($this->sender)) {
			$user = new User();

            $user->read($this->sender);
			
			$this->folder = MessageFolder::SENT;

            $this->create();
			
			$mailer = new Mailer();
			
			$mailer->sendTemplate(EmailTemplate::SELLERMESSAGE, $this->recipient, $user->getUsername(), $this->message);
		}
	}
	
	public function getExcerpt() {
		$message = strip_tags(str_replace('<br>', ' ', $this->message));
		if (strlen($message) > 50) {
			return substr($message, 0, 50) . '...';
		}
		return $message;
	}
	
	public static function getMessagesByUser($uid, $folder = MessageFolder::INBOX, $showDeleted = false) {
		$messages = array();
		
		switch ($folder) {
			case MessageFolder::INBOX:
				$q = DB::getInstance()->prepare('SELECT id FROM messages WHERE folder = ? AND recipient = ? AND (deleted = ? OR deleted = ?) ORDER BY date DESC');
				break;
			case MessageFolder::SENT:
				$q = DB::getInstance()->prepare('SELECT id FROM messages WHERE folder = ? AND sender = ? AND (deleted = ? OR deleted = ?) ORDER BY date DESC');
				break;
			case MessageFolder::PRODUCTDELIVERY:
				$q = DB::getInstance()->prepare('SELECT id FROM messages WHERE folder = ? AND sender = ? AND (deleted = ? OR deleted = ?) ORDER BY date DESC');
				break;
		}

		$q->execute(array($folder, $uid, $showDeleted, false));
		$q = $q->fetchAll();

		foreach ($q as $p) {
			$message = new Message();
			if ($message->read($p['id'], $showDeleted)) {
				$messages[] = $message;
			}
		}
		
		return $messages;
	}

    public function getRead() {
        return $this->read;
    }

    public function getDate() {
        return $this->date;
    }

    public function getId() {
        return $this->id;
    }

    public function getSender() {
        return $this->sender;
    }

    public function getFolder() {
        return $this->folder;
    }

    public function setFolder($folder){
        $this->folder = $folder;
    }

    public function setSender($sender) {
        $this->sender = $sender;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setRecipient($recipient) {
        $this->recipient = $recipient;
    }

    public function isOwner($id) {
        if ($this->folder == MessageFolder::INBOX) {
            return $id == $this->recipient;
        }

        if ($this->folder == MessageFolder::SENT || $this->folder == MessageFolder::PRODUCTDELIVERY) {
            return $id == $this->sender;
        }
    }

    public function setRead() {
        $this->read = true;
    }

    public function setDeleted() {
        $this->deleted = true;
    }

    public function isDeleted() {
        return $this->deleted;
    }

    public function getRecipient() {
        return $this->recipient;
    }

}