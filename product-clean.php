<?php
//TODO: Move to dedicated admin panel
//A very, very rough script for cleaning out products that are against TOS.

/*
require_once('lib/functions.php');
die();
if (getRealIp() != '75.88.69.168') {
    die();
}

$search = array('netflix', 'origin', 'hulu', 'spotify', 'hbo');

$x = 'AND (p.title LIKE "%' . implode('%" OR p.title LIKE "%', $search) . '%")';

echo 'SELECT p.id, p.title, u.username, u.email FROM `products` AS `p` JOIN `users` AS `u` ON (u.id = p.seller_id) WHERE p.deleted = 0 AND p.currency LIKE "%0%" ' . $x;


$q = DB::getInstance()->prepare('SELECT p.id, p.title, u.username, u.email FROM `products` AS `p` JOIN `users` AS `u` ON (u.id = p.seller_id) WHERE p.deleted = 0 AND p.currency LIKE "%0%" ' . $x);
$q->execute();
$q = $q->fetchAll();




foreach ($q as $p) {
    //echo $p['title'] . '<br>';

    $message = 'Hi ' . $p['username'] . ',

    We have determined that your product <b>' . $p['title'] . '</b> is in violation of both our and PayPal\'s policies.

    Your product has been removed.
    ';

    $q = DB::getInstance()->prepare('UPDATE products SET deleted = 1 WHERE id = ?');
    $q->execute(array($p['id']));

    $mailer = new Mailer();

    $mailer->send($p['email'], $message, 'Product Content Violation Notice');
}

echo 'Removed ' . count($q) . ' products';*/