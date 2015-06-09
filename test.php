<?php
die();
require_once('lib/functions.php');

$x = DB::getInstance()->prepare('SELECT COUNT(*) AS `Rows`, `url` FROM `products` GROUP BY `url` ORDER BY `Rows` DESC ');
$x->execute();

$x = $x->fetchAll();

foreach ($x as $y) {
    if ($y['Rows'] != 1) {
        echo '1';

        $t = '';

        while (true) {
            $t = generateRandomString(5);

            $x = DB::getInstance()->prepare('SELECT id FROM products WHERE url = ?');
            $x->execute(array($t));

            if (count($x->fetchAll()) == 0) {
                break;
            }
        }

        $x = DB::getInstance()->prepare('UPDATE products SET url = ? WHERE url = ? LIMIT 1');
        $x->execute(array($t, $y['url']));
    }
}