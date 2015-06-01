<?php

class LicensingAnnouncement {
    private $id;
    private $title;
    private $description;
    private $postedOn;
    private $application;

    function __construct()
    {
        $this->id = 0;
        $this->title = '';
        $this->description = '';
        $this->application = 0;
    }

    public function create(){
        $this->postedOn = date('Y-m-d H:i:s');
        $p = DB::getInstance()->prepare("INSERT INTO `licensing_announcements`(`Title`, `Description`, `PostedOn`, `Application`) VALUES (?,?,?,?);");
        $p->execute(array($this->title, $this->description, $this->postedOn, $this->application));

        $p = DB::getInstance()->prepare("SELECT id FROM licensing_announcements WHERE PostedOn = ? And Title = ? And Description = ? And Application = ? ORDER BY id DESC LIMIT 1");
        $p ->execute(array($this->postedOn, $this->title, $this->description, $this->application));
        $q = $p->fetchAll();

        if(count($q) != 1){
            return false;
        }

        $this->id = $q[0]['id'];

        return true;
    }

    public function update(){
        $p = DB::getInstance()->prepare("UPDATE licensing_announcements SET Title = ?, Description = ? WHERE id = ?");
        $p->execute(array($this->title, $this->description, $this->id));
    }

    public function read($id){
        $p = DB::getInstance()->prepare("SELECT * FROM licensing_announcements WHERE id = ?");
        $p->execute(array($id));
        $q = $p->fetchAll();

        if(count($q) != 1){
            return false;
        }

        $ann = $q[0];
        $this->title = $ann['Title'];
        $this->description = $ann['Description'];
        $this->id = $ann['id'];
        $this->postedOn = $ann['PostedOn'];
        $this->application = $ann['Application'];

        return true;
    }

    public static function getAnnouncementsByLicensingProduct($lpid){
        $announcements = array();

        $p = DB::getInstance()->prepare("SELECT * FROM licensing_announcements WHERE Application = ?");
        $p->execute(array($lpid));
        $q = $p->fetchAll();

        foreach($q as $a){
            $ann = new LicensingAnnouncement();
            if($ann->read($a['id'])){
                $announcements[] = $ann;
            }
        }

        return $announcements;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPostedOn()
    {
        return $this->postedOn;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }
}