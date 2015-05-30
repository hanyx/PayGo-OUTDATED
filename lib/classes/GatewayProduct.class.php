<?php
class GatewayProduct {
    private $id;
    private $ipn_url;
    private $address_to;
    private $currency;
    private $fiat;
    private $after_success_url;
    private $status;
    private $date;
    private $processor_txid;
    private $txid;
    private $custom;

    public function __construct() {
        $this->id = 0;
        $this->ipn_url = '';
        $this->address_to = '';
        $this->currency = 0;
        $this->fiat = 0.00;
        $this->after_success_url = '';
        $this->status = '';
        $this->date = '';
        $this->processor_txid = '';
        $this->txid = '';
        $this->custom = '';
    }

    public function read($id){
        $q = DB::getInstance()->prepare("SELECT id, ipn_url, address_to, currency, fiat, after_success_url, status, `date`, processor_txid, txid, custom FROM gateway_products WHERE id = ?");
        $q->execute(array($id));
        $q = $q->fetchAll();

        if(count($q) != 1) {
            return false;
        }

        $product = $q[0];

        $this->id = $product['id'];
        $this->ipn_url = $product['ipn_url'];
        $this->address_to = $product['address_to'];
        $this->currency = $product['currency'];
        $this->fiat = $product['fiat'];
        $this->after_success_url = $product['after_success_url'];
        $this->status = $product['status'];
        $this->date = $product['date'];
        $this->processor_txid = $product['processor_txid'];
        $this->txid = $product['txid'];
        $this->custom = $product['custom'];

        return true;
    }

    public function readByTxid($txid){
        $q = DB::getInstance()->prepare("SELECT id, ipn_url, address_to, currency, fiat, after_success_url, status, `date`, processor_txid, txid, custom FROM gateway_products WHERE txid = ?");
        $q->execute(array($txid));
        $q = $q->fetchAll();

        if(count($q) != 1) {
            return false;
        }

        $product = $q[0];

        $this->id = $product['id'];
        $this->ipn_url = $product['ipn_url'];
        $this->address_to = $product['address_to'];
        $this->currency = $product['currency'];
        $this->fiat = $product['fiat'];
        $this->after_success_url = $product['after_success_url'];
        $this->status = $product['status'];
        $this->date = $product['date'];
        $this->processor_txid = $product['processor_txid'];
        $this->txid = $product['txid'];
        $this->custom = $product['custom'];

        return true;
    }

    public function create(){
        while(true) {
            $this->txid = generateRandomString(64);

            $gp = new GatewayProduct();
            if(!$gp->readByTxid($this->txid)){
                break;
            }
        }

        $this->date = date('Y-m-d H:i:s');

        $q = DB::getInstance()->prepare('INSERT INTO `gateway_products`(`ipn_url`, `address_to`, `currency`, `fiat`, `after_success_url`, `status`, `date`, `processor_txid`, `txid`, `custom`) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array($this->ipn_url, $this->address_to, $this->currency, $this->fiat, $this->after_success_url, $this->status, $this->date, $this->processor_txid, $this->txid, $this->custom));

        $this->readByTxid($this->txid);
    }

    public function update(){
        $q = DB::getInstance()->prepare('UPDATE gateway_products SET ipn_url = ?, address_to = ?, currency = ?, fiat = ?, after_success_url = ?, status = ?, processor_txid = ?, custom = ? WHERE txid = ?');
        $q->execute(array($this->ipn_url, $this->address_to, $this->currency, $this->fiat, $this->after_success_url, $this->status, $this->processor_txid, $this->custom, $this->txid));
    }

    public function getId() {
        return $this->id;
    }

    public function getIpnUrl() {
        return $this->ipn_url;
    }

    public function setIpnUrl($ipn_url){
        if(filter_var($ipn_url, FILTER_VALIDATE_URL)) {
            $this->ipn_url = $ipn_url;
        }
    }

    public function getAddressTo(){
        return $this->address_to;
    }

    public function setAddressTo($address_to) {
        $this->address_to = $address_to;
    }

    public function getCurrency(){
        return $this->currency;
    }

    public function setCurrency($currency){
        $this->currency = $currency;
    }

    public function getFiat(){
        return $this->fiat;
    }

    public function setFiat($fiat){
        $this->fiat = $fiat;
    }

    public function getAfterSuccessUrl(){
        return $this->after_success_url;
    }

    public function setAfterSuccessUrl($after_success_url){
        $this->after_success_url = $after_success_url;
    }

    public  function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getDate(){
        return $this->date;
    }

    public function getProcessorTxid(){
        return $this->processor_txid;
    }

    public function setProcessorTxid($processor_txid) {
        $this->processor_txid = $processor_txid;
    }

    public function getTxid(){
        return $this->txid;
    }

    public function getCustom(){
        return $this->custom;
    }

    public function setCustom($custom){
        $this->custom = $custom;
    }
}