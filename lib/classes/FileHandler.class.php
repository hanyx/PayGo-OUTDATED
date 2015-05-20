<?php
class FileHandler {

    private $id;
    private $owner;
    private $fileId;
    private $extension;

    public function __construct() {
        $this->id = 0;
        $this->owner = 0;
        $this->fileId = '';
        $this->extension = '';
    }

    public function create() {
        while (true) {
            $this->fileId = generateRandomString();

            $fileHandler = new FileHandler();

            if (!$fileHandler->readByFileId($this->fileId)) {
                break;
            }
        }

        $q = DB::getInstance()->prepare('INSERT into files (owner, file_id, extension) VALUES (?, ?, ?)');
        $q->execute(array($this->owner, $this->fileId, $this->extension));

        $this->readByFileId($this->fileId);
    }

    public function read($id) {
        $q = DB::getInstance()->prepare('SELECT id, owner, file_id, extension FROM files WHERE id = ?');
        $q->execute(array($id));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        $this->id = $q[0]['id'];
        $this->owner = $q[0]['owner'];
        $this->fileId = $q[0]['file_id'];
        $this->extension = $q[0]['extension'];

        return true;
    }

    public function readByFileId($fileId) {
        $q = DB::getInstance()->prepare('SELECT id FROM files WHERE file_id = ?');
        $q->execute(array($fileId));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }

    public function setOwner($owner) {
        $this->owner = $owner;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
    }

    public function getFile() {
        return $this->fileId . '.' . $this->extension;
    }

    public function getFileId() {
        return $this->fileId;
    }

    public function getId() {
        return $this->id;
    }

}