<?php

class Coupon {
    private $id;
    private $name;
    private $reduction;
    private $used_amount;
    private $max_used_amount;
    private $product_id;

    public function __construct()
    {
        $this->id = 0;
        $this->name = '';
        $this->reduction = 0;
        $this->used_amount = 0;
        $this->max_used_amount = 0;
        $this->product_id = 0;
    }

    public function create(){
        $q = DB::getInstance()->prepare("INSERT INTO coupons(name, reduction, used_amount, max_used_amount, product_id) VALUES (?,?,?,?,?)");
        $q->execute(array($this->name, $this->reduction, 0, $this->max_used_amount, $this->product_id));
    }

    public function read($id)
    {
        $q = DB::getInstance()->prepare('SELECT * FROM coupons WHERE id = ?');

        $q->execute(array($id));
        $q = $q->fetchAll();

        if(count($q) != 1){
            return false;
        }

        $this->id = $q[0]['id'];
        $this->max_used_amount = $q[0]['max_used_amount'];
        $this->reduction = $q[0]['reduction'];
        $this->name = $q[0]['name'];
        $this->product_id = $q[0]['product_id'];
        $this->used_amount = $q[0]['used_amount'];

        return true;
    }

    public static function getCouponsByProduct($pid)
    {
        $coupons = array();

        $q = DB::getInstance()->prepare('SELECT id FROM coupons WHERE product_id = ?');
        $q->execute(array($pid));
        $q = $q->fetchAll();

        foreach($q as $c){
            $coupon = new Coupon();
            $coupon->read($c['id']);

            if($coupon != null){
                $coupons[] = $coupon;
            }
        }
        return $coupons;
    }

    public function getId(){
        return $this->id;
    }

    public function  getName(){
        return $this->name;
    }

    public function getReduction(){
        return $this->reduction;
    }

    public function getUsedAmount(){
        return $this->used_amount;
    }

    public function getMaxUsedAmount(){
        return $this->max_used_amount;
    }

    public function getProductId(){
        return $this->product_id;
    }

    public function getProduct(){
        return Product::getProduct($this->product_id);
    }

    public function setName($name){
        $this->name = $name;
    }

    public function setReduction($reduction){
        $this->reduction = $reduction;
    }

    public function setUsedAmount($usedAmount){
        $this->used_amount = $usedAmount;
    }

    public function setMaxUsedAmount($maxUsedAmount){
        $this->max_used_amount = $maxUsedAmount;
    }

    public function setProductId($productId){
        $this->product_id = $productId;
    }
}