<?php

class LicensingProduct {
    private $id;
    private $title;
    private $description;
    private $maxIpAddresses;
    private $isReady;
    private $createdOn;
    private $md5;
    private $coreProgram;
    private $lastUpdated;
    private $status;
    private $preloaderKey;
    private $loginForm;
    private $isJunked;
    private $logo;

    public function __construct() {
        $this->id = 0;
        $this->title = '';
        $this->description = '';
        $this->maxIpAddresses= 0;
        $this->isReady = false;
        $this->md5 = '';
        $this->status = 0;
        $this->preloaderKey = '';
        $this->isJunked = true;
        $this->coreProgram = array();
        $this->loginForm = array();
        $this->logo = array();
    }

    public function create(){
        $this->createdOn = date('Y-m-d H:i:s');
        $this->lastUpdated = $this->createdOn;

        while (true) {
            $this->preloaderKey = generateRandomString(64);

            $lp = new LicensingProduct();

            if (!$lp->readByPreloaderKey($this->preloaderKey)) {
                break;
            }
        }

        $p = DB::getInstance()->prepare("INSERT INTO `licensing_products`(`Title`, `Description`, `MaxIpAddresses`, `IsReady`, `CreatedOn`, `Md5`, `CoreProgram`, `LastUpdated`, `Status`, `PreloaderKey`, `LoginForm`, `IsJunked`, `Logo`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $p->execute(array($this->title, $this->description,$this->maxIpAddresses,$this->isReady,$this->createdOn,$this->md5,$this->coreProgram,$this->lastUpdated,$this->status,$this->preloaderKey,$this->loginForm,$this->isJunked,$this->logo));
    }

    public function update(){
        $p = DB::getInstance()->prepare("UPDATE licensing_products SET Title = ?, Description = ?, MaxIpAddresses = ?, IsReady = ?, Md5 = ?, CoreProgram = ?, LastUpdated = ?, Status = ?, LoginForm = ?, IsJunked = ?, Logo = ? WHERE id = ?");
        $p->execute(array($this->title, $this->description, $this->maxIpAddresses, $this->isReady, $this->md5, $this->coreProgram, $this->lastUpdated, $this->status, $this->loginForm, $this->isJunked, $this->logo, $this->id));
    }

    public function read($id){
        $p = DB::getInstance()->prepare("SELECT * FROM `licensing_products` WHERE id = ? ");
        $p->execute(array($id));
        $q = $p->fetchAll();

        if(count($q) != 1) {
            return false;
        }

        $product = $q[0];

        $this->id = $product['id'];
        $this->title = $product['Title'];
        $this->description = $product['Description'];
        $this->maxIpAddresses = $product['MaxIpAddresses'];
        $this->isReady = $product['IsReady'];
        $this->createdOn = $product['CreatedOn'];
        $this->md5 = $product['Md5'];
        $this->coreProgram = $product['CoreProgram'];
        $this->lastUpdated = $product['LastUpdated'];
        $this->status = $product['Status'];
        $this->preloaderKey = $product['PreloaderKey'];
        $this->loginForm = $product['LoginForm'];
        $this->isJunked = $product['IsJunked'];
        $this->logo = $product['Logo'];

        return true;
    }

    public function readByPreloaderKey($preloaderKey){
        $p = DB::getInstance()->prepare("SELECT id FROM `licensing_products` WHERE PreloaderKey = ?");
        $p->execute(array($preloaderKey));
        $q = $p->fetchAll();

        if(count($q) != 1) {
            return false;
        }

        return $this->read($q[0]['id']);
    }

    public function getId(){
        return $this->id;
    }

    public function getTitle(){
        return $this->title;
    }

    public function setTitle($title){
        $this->title = $title;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($desc){
        $this->description = $desc;
    }

    public function getMaxIpAddresses()
    {
        return $this->maxIpAddresses;
    }
    public function setMaxIpAddresses($maxIpAddresses)
    {
        $this->maxIpAddresses = $maxIpAddresses;
    }

    public function getIsReady()
    {
        return $this->isReady;
    }

    public function setIsReady($isReady)
    {
        $this->isReady = $isReady;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function getMd5()
    {
        return $this->md5;
    }

    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    public function getCoreProgram()
    {
        return $this->coreProgram;
    }

    public function setCoreProgram($coreProgram)
    {
        $this->coreProgram = $coreProgram;
    }

    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getPreloaderKey()
    {
        return $this->preloaderKey;
    }

    public function getLoginForm()
    {
        return $this->loginForm;
    }

    public function setLoginForm($loginForm)
    {
        $this->loginForm = $loginForm;
    }

    public function getIsJunked()
    {
        return $this->isJunked;
    }

    public function setIsJunked($isJunked)
    {
        $this->isJunked = $isJunked;
    }

    public function getLogo()
    {
        return $this->logo;
    }


    public function setLogo($logo)
    {
        $this->logo = $logo;
    }
}