<?php
class File {

    private $id;
    private $deleted;
    private $owner;
    private $name;
    private $fileId;
    private $extension;
    private $hidden;

    public function __construct() {
        $this->id = 0;
        $this->deleted = false;
        $this->owner = 0;
        $this->name = '';
        $this->fileId = '';
        $this->extension = '';
        $this->hidden = false;
    }

    public function create() {
        while (true) {
            $this->fileId = generateRandomString();

            $fileHandler = new File();

            if (!$fileHandler->readByFileId($this->fileId)) {
                break;
            }
        }

        $q = DB::getInstance()->prepare('INSERT into files (deleted, owner, name, file_id, extension, hidden) VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($this->deleted, $this->owner, $this->name, $this->fileId, $this->extension, $this->hidden));

        $this->readByFileId($this->fileId);
    }

    public function read($id, $showDeleted = false) {
        $q = DB::getInstance()->prepare('SELECT id, deleted, owner, name, file_id, extension, hidden FROM files WHERE id = ? AND (deleted = ? OR deleted = ?)');
        $q->execute(array($id, $showDeleted, false));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        $this->id = $q[0]['id'];
        $this->deleted = $q[0]['deleted'];
        $this->owner = $q[0]['owner'];
        $this->name = $q[0]['name'];
        $this->fileId = $q[0]['file_id'];
        $this->extension = $q[0]['extension'];
        $this->hidden = $q[0]['hidden'];

        return true;
    }

    public function readByFileId($fileId, $showDeleted = false) {
        $q = DB::getInstance()->prepare('SELECT id FROM files WHERE file_id = ? AND (deleted = ? OR deleted = ?)');
        $q->execute(array($fileId, $showDeleted, false));
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

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public static function getFilesByUser($uid, $showDeleted = false) {
        $files = array();

        $q = DB::getInstance()->prepare('SELECT id FROM files WHERE owner = ? AND (deleted = ? OR deleted = ?)');
        $q ->execute(array($uid, $showDeleted, false));
        $q = $q->fetchAll();

        foreach ($q as $f) {
            $file = new File();

            if ($file->read($f['id'], $showDeleted)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function isHidden() {
        return $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

}