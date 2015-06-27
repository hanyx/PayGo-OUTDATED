<?php
if (isset($_POST['token']) && count($url) == 2) {
    switch ($url['1']) {
        case 'stats':
            if ($_POST['token'] == '8YaQGE8sEuY63VK6c2viBBCR') {
                $return = '';

                $q = DB::getInstance()->query('SELECT sum(fiat) AS `fiat`, count(id) as `count` FROM orders WHERE completed = 1');
                $q->execute();
                $q = $q->fetchAll();

                $return .= number_format($q[0]['count']) . ' transactions totalling $' . number_format($q[0]['fiat'], 2) . "\r\n";

                $q = DB::getInstance()->query('SELECT count(id) as `count` FROM users');
                $q->execute();
                $q = $q->fetchAll();

                $return .= number_format($q[0]['count']) . " users\r\n";

                $q = DB::getInstance()->query('SELECT count(id) as `count` FROM products WHERE deleted = 0');
                $q->execute();
                $q = $q->fetchAll();

                $return .= number_format($q[0]['count']) . " products\r\n";

                echo $return;
            }
            break;
    }
}