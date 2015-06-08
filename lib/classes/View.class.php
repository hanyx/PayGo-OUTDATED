<?php

class View {

    private $id;
    private $productId;
    private $date;
    private $ip;
    private $referrer;

    public function __construct() {
        $this->id = 0;
        $this->productId = 0;
        $this->date = '';
        $this->ip = '';
        $this->referrer = '';
    }

    public function create() {
        $this->date = date('Y-m-d H:i:s');

        $q = DB::getInstance()->prepare('INSERT into views (product_id, date, ip, referrer) VALUES (?, ?, ?, ?)');
        $q->execute(array($this->productId, $this->date, $this->ip, $this->referrer));
    }

    public function read($id) {
        $q = DB::getInstance()->prepare('SELECT id, product_id, date, ip, referrer FROM views WHERE id = ?');
        $q->execute(array($id));
        $q = $q->fetchAll();

        if (count($q) != 1) {
            return false;
        }

        $this->id = $q[0]['id'];
        $this->productId = $q[0]['product_id'];
        $this->date = $q[0]['date'];
        $this->ip = $q[0]['ip'];
        $this->referrer = $q[0]['referrer'];

        return true;
    }

    public static function getViewsByUser($uid) {
        $views = array();

        $q = DB::getInstance()->prepare('SELECT v.id FROM views AS `v` JOIN products AS `p` ON (p.id = v.product_id) JOIN users AS `u` ON (u.id = p.seller_id) WHERE u.id = ? ORDER BY date DESC');
        $q->execute(array($uid));
        $q = $q->fetchAll();

        foreach ($q as $p) {
            $view = new View();
            if ($view->read($p['id'])) {
                $views[] = $view;
            }
        }

        return $views;
    }

    public static function getViewsByProduct($id) {
        $views = array();
        $q = DB::getInstance()->prepare('SELECT v.id FROM views AS `v` JOIN products AS `p` ON (p.id = v.product_id) WHERE p.id = ? ORDER BY date DESC');
        $q->execute(array($id));
        $q = $q->fetchAll();
        foreach ($q as $p) {
            $view = new View();
            if ($view->read($p['id'])) {
                $views[] = $view;
            }
        }
        return $views;
    }

    public function getProductId() {
        return $this->productId;
    }

    public function setProductId($productId) {
        $this->productId = $productId;
    }

    public function getDate() {
        return $this->date;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

    public function getReferrer() {
        return $this->referrer;
    }

    public function setReferrer($referrer) {
        $this->referrer = $referrer;
    }

}