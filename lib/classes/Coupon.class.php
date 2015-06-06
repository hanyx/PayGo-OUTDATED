<?php

class Coupon {
    private $id;
    private $name;
    private $reduction;
    private $used_amount;
    private $max_used_amount;
    private $seller_id;
    private $products;

    public function __construct()
    {
        $this->id = 0;
        $this->name = '';
        $this->reduction = 0;
        $this->used_amount = 0;
        $this->max_used_amount = 0;
        $this->seller_id = 0;
        $this->products = array();
    }

    public function create(){
        $q = DB::getInstance()->prepare("INSERT INTO product_coupons(name, reduction, used_amount, max_used_amount, seller_id, products) VALUES (?,?,?,?,?,?)");
        $q->execute(array($this->name, $this->reduction, 0, $this->max_used_amount, $this->seller_id, implode(",",$this->products)));
    }

    public function update(){
        $q = DB::getInstance()->prepare("UPDATE product_coupons SET name = ?, max_used_amount = ?, reduction = ?, products = ? WHERE id = ?");
        $q->execute(array($this->name, $this->max_used_amount, $this->reduction, implode(",",$this->products), $this->id));
    }

    public function setProducts($products){
        $this->products = $products;
    }

    public function getProducts(){
        return $this->products;
    }

    public function read($id)
    {
        $q = DB::getInstance()->prepare('SELECT * FROM product_coupons WHERE id = ?');

        $q->execute(array($id));
        $q = $q->fetchAll();

        if(count($q) != 1){
            return false;
        }

        $this->id = $q[0]['id'];
        $this->max_used_amount = $q[0]['max_used_amount'];
        $this->reduction = $q[0]['reduction'];
        $this->name = $q[0]['name'];
        $this->seller_id = $q[0]['seller_id'];
        $this->used_amount = $q[0]['used_amount'];
        $this->products = explode(',', $q[0]['products']);

        return true;
    }

    public static function getCouponsByUser($uid)
    {
        $coupons = array();

        $q = DB::getInstance()->prepare('SELECT id FROM product_coupons WHERE seller_id = ?');
        $q->execute(array($uid));
        $q = $q->fetchAll();

        foreach($q as $c){
            $coupon = new Coupon();

            if($coupon->read($c['id'])){
                $coupons[] = $coupon;
            }
        }
        return $coupons;
    }

    public static function getCoupon($id){
        $coupon = new Coupon();

        if(!$coupon->read($id)){
            return false;
        }

        return $coupon;
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

    public function getSellerId(){
        return $this->seller_id;
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

    public function setSellerId($sellerId){
        $this->seller_id = $sellerId;
    }

    public function getOrders(){
        return Order::getOrdersByCoupon($this->id);
    }
}