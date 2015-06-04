<?php
class Download {

    private $id;
    private $link;
    private $fileId;
    private $ip;

    public function __construct() {
        $this->id = 0;
        $this->link = '';
        $this->filesId = 0;
        $this->ip = '';
    }

    public function create() {
        while (true) {
            $this->link = generateRandomString();

            $download = new Download();

            if (!$download->readByLink($this->link)) {
                break;
            }
        }

        $q = DB::getInstance()->prepare('INSERT into downloads (link, file_id, ip) VALUES (?, ?, ?)');
        $q->execute(array($this->link, $this->fileId, $this->ip));

        $this->readByLink($this->link);
    }

    public function read($id) {
        $q = DB::getInstance()->prepare('SELECT id, link, file_id, ip');
        $q->execute(array($id));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        $this->id = $q[0]['id'];
        $this->link = $q[0]['link'];
        $this->fileId = $q[0]['file_id'];
        $this->ip = $q[0]['ip'];

        return true;
    }

    public function readByLink($link) {
        $q = DB::getInstance()->prepare('SELECT id FROM downloads WHERE link');
        $q->execute(array($link));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }

    public function getLink() {
        return $this->link;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function getFileId() {
        return $this->fileId;
    }

    public function setFileId($fileId) {
        $this->fileId = $fileId;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

}