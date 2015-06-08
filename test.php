<?php
set_time_limit(0);
require_once('lib/functions.php');


$order = new Order();

$order->read(31619, false);

$order->process();






$db = DB::getInstance();
$db2 = DB2::getInstance();

/*
$x = $db->prepare('SELECT id, title, description, currency, custom_delivery, affiliate_secondary_link FROM products');
$x->execute();

foreach ($x as $y) {

    $currency = array();

    if (strpos($y['currency'], 'USD') !== false) {
        $currency[] = ProductCurrency::PAYPAL;
    }

    if (strpos($y['currency'], 'PP') !== false) {
        $currency[] = ProductCurrency::PAYPAL;
    }

    if (strpos($y['currency'], 'BTC') !== false) {
        $currency[] = ProductCurrency::BITCOIN;
    }

    if (strpos($y['currency'], 'LTC') !== false) {
        $currency[] = ProductCurrency::LITECOIN;
    }

    if (strpos($y['currency'], 'OMC') !== false) {
        $currency[] = ProductCurrency::OMNICOIN;
    }

    $x = $db->prepare('UPDATE products SET url = ?, title = ?, description = ?, currency = ?, custom_delivery = ?, affiliate_secondary_link = ? WHERE id = ?');
    $x->execute(array(generateRandomString(), htmlspecialchars($y['title'], ENT_QUOTES), stripTags($y['description']), implode(',', $currency), htmlspecialchars($y['custom_delivery'], ENT_QUOTES), htmlspecialchars($y['affiliate_secondary_link'], ENT_QUOTES), $y['id']));
}


*/





/*
$x = $db2->prepare('SELECT secret, item_name FROM ebooks ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT old_id FROM products WHERE title = ?');
    $x->execute(array($y['item_name']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('SELECT file_id FROM products_files WHERE product_id = ?');
        $x->execute(array($t[0]['old_id']));

        $t = $x->fetchAll();
        if (count($t) == 1) {

            $x = $db->prepare('INSERT INTO downloads (link, file_id) VALUES (?, ?)');
            $x->execute(array($y['secret'], $t[0]['file_id']));
        }
    }
}
*/




/*
$x = $db2->prepare('SELECT link, ip, item_name FROM links ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT old_id FROM products WHERE title = ?');
    $x->execute(array($y['item_name']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('SELECT file_id FROM products_files WHERE product_id = ?');
        $x->execute(array($t[0]['old_id']));

        $t = $x->fetchAll();

        $x = $db->prepare('INSERT INTO downloads (link, file_id, ip) VALUES (?, ?, ?)');
        $x->execute(array($y['link'], $t[0]['file_id'], $y['ip']));

    }
}*/


/*
$x = $db2->prepare('SELECT send_date, name, seller_id, message FROM buyer_emails ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT id FROM users WHERE old_id = ?');
    $x->execute(array($y['seller_id']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('INSERT INTO messages (folder, recipient, sender, message, date) VALUES (?, ?, ?, ?, ?)');
        $x->execute(array(MessageFolder::PRODUCTDELIVERY, $y['name'], $y['seller_id'], $y['message'], $y['send_date']));

    }
}*/




/*
$x = $db2->prepare('SELECT recipient, sender, message, send_date, name FROM messages_inbox ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT id FROM users WHERE old_id = ?');
    $x->execute(array($y['recipient']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('INSERT INTO messages (folder, recipient, sender, message, date) VALUES (?, ?, ?, ?, ?)');
        $x->execute(array(MessageFolder::INBOX, $y['recipient'], $y['sender'], $y['message'], $y['send_date']));

    }
}*/






/*$x = $db2->prepare('SELECT recipient, sender, message, send_date, name FROM messages_sent ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT id FROM users WHERE old_id = ?');
    $x->execute(array($y['sender']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('INSERT INTO messages (folder, recipient, sender, message, date) VALUES (?, ?, ?, ?, ?)');
        $x->execute(array(MessageFolder::SENT, $y['recipient'], $y['sender'], $y['message'], $y['send_date']));

    }
}*/







/*
$x = $db2->prepare('SELECT date, ip, referrer, product_id FROM views ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT id FROM products WHERE old_id = ?');
    $x->execute(array($y['product_id']));

    $t = $x->fetchAll();

    if (count($t) == 1) {

        $x = $db->prepare('INSERT INTO views (product_id, date, ip, referrer) VALUES (?, ?, ?, ?)');
        $x->execute(array($y['product_id'], $y['date'], $y['ip'], $y['referrer']));

    }
}*/





/*
$x = $db2->prepare('SELECT tx, name, txn_id, currency2, amount1, amount2, buy_date, ip, item_id, question1, question2, question3, question4 FROM buyer ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT id FROM products WHERE old_id = ?');
    $x->execute(array($y['item_id']));

    $t = $x->fetchAll();

    if (count($t) == 1) {
        if (strpos($y['currency2'], 'BTC') === 0) {
            $currency = ProductCurrency::BITCOIN;
        } else if (strpos($y['currency2'], 'LTC)') === 0) {
            $currency = ProductCurrency::LITECOIN;
        } else if (strpos($y['currency2'], 'OMC') === 0) {
            $currency = ProductCurrency::OMNICOIN;
        } else {
            $currency = ProductCurrency::PAYPAL;
        }




        $x = $db->prepare('INSERT INTO orders (completed, txid, date, product_id, currency, fiat, native, email, ip, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $x->execute(array(true, $y['tx'] == '' ? $y['txn_id'] : $y['tx'], $y['buy_date'], $y['item_id'], $currency, $y['amount1'], $y['amount2'], $y['name'], $y['ip'], 1));

        $x = $db->prepare('SELECT id FROM orders WHERE txid = ? AND date = ?');
        $x->execute(array($y['tx'] == '' ? $y['txn_id'] : $y['tx'], $y['buy_date']));

        $abc = $x->fetchColumn();

        $f = 0;
        for ($t = 1; $t <= 4; $t++) {
            if ($y['question' . $t] != '') {
                $x = $db->prepare('SELECT question FROM product_questions WHERE product_id = ? AND question_index = ?');
                $x->execute(array($y['item_id'], $f));

                $dac = $x->fetchColumn();


                $z = $db->prepare('INSERT INTO order_questions (order_id, question_index, question, response) VALUES (?, ?, ?, ?)');
                $z->execute(array($abc, $f, $dac, $y['question' . $t]));
                $f++;
            }
        }
    }
}*/







/*
$x = $db2->prepare('SELECT id, email, product, seller, numSales, numUnpaid, password FROM affiliates ORDER BY id');
$x->execute();

foreach ($x as $y) {

    $x = $db->prepare('SELECT old_id, price FROM products WHERE title = ? AND seller_id = ?');
    $x->execute(array($y['product'], $y['seller']));

    $t = $x->fetchAll();

    if (count($t) == 1) {
        $x = $db->prepare('INSERT INTO affiliates (email, password, product_id, orders, unpaid_orders, unpaid_fiat) VALUES (?, ?, ?, ?, ?, ?)');
        $x->execute(array($y['email'], password_hash($y['password'], 1), $t[0]['old_id'], $y['numSales'], $y['numUnpaid'], $y['numUnpaid'] * $t[0]['price']));
    }
}*/






/*
$x = $db2->prepare('SELECT id, seller_id, title, description, price, type, aes_decrypt(url, ?) as `url`, aes_decrypt(toSend, ?) as `toSend`, currency, affiliate_enabled, affiliate_percent, secondaryaff, netseal_1, netseal_2, netseal_3, netseal_4, netseal_5, aff_id, display, custom_question_1, custom_question_2, custom_question_3, custom_question_4, custom_message, paypal_subscription_duration, paypal_subscription_duration_code, paypal_needs_shipping FROM products ORDER BY id');
$x->execute(array('SX43ndyq', 'SX43ndyq'));



$x = $x->fetchAll();

foreach ($x as $y) {
    $z = $db->prepare('SELECT id FROM users WHERE old_id = ?');
    $z->execute(array($y['seller_id']));

    if (count($z->fetchAll()) != 1) {
        continue;
    }

    if ($y['type'] == 0 || $y['type'] == 3) {
        if (!file_exists('/var/www/payivy.com/private/uploads/' . $y['url'])) {
            echo $y['url'] . '<br>';
            continue;
        }
        $z = $db->prepare('INSERT INTO files (owner, name, file_id) VALUES (?, ?, ?)');
        $z->execute(array($y['seller_id'], $y['url'], $y['url']));

        

        $z = $db->prepare('SELECT id FROM files WHERE file_id = ?');
        $z->execute(array($y['url']));
        $b = $z->fetchColumn();

        

        $z = $db->prepare('INSERT INTO products_files (product_id, file_id) VALUES (?, ?)');
        $z->execute(array($y['id'], $b));

        
    } else if ($y['type'] == 1) {
        $z = $db->prepare('INSERT INTO products_serials (product_id, serials) VALUES (?, ?)');
        $z->execute(array($y['id'], $y['toSend']));

        
    }  else if ($y['type'] == 2) {
        $f = 0;
        for ($t = 1; $t <= 4; $t++) {
            if ($y['netseal_' . $t] != '') {
                $yd = explode('`', $y['netseal_' . $t]);

                $z = $db->prepare('INSERT INTO products_netseals (seal_index, product_id, download, time, points, type, track, api) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $z->execute(array($f, $y['id'], $yd[0], $yd[1], $yd[2], $yd[3], $yd[4], $yd[5]));
                $f++;

                
            }
        }
    }

    $z = $db->prepare('INSERT INTO products (seller_id, title, description, price, type, currency, visible, custom_delivery, pp_sub_length, pp_sub_unit, require_shipping, affiliate_enabled, affiliate_percent, affiliate_secondary_link, affiliate_id, old_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $z->execute(array($y['seller_id'], $y['title'], $y['description'], $y['price'], $y['type'] == '3' ? '0' : $y['type'], $y['currency'], $y['display'], $y['custom_message'], $y['paypal_subscription_duration'] == '' ? '0' : $y['paypal_subscription_duration'], $y['paypal_subscription_duration_code'] == '' ? '0' : $y['paypal_subscription_duration_code'], $y['paypal_needs_shipping'], $y['affiliate_enabled'], $y['affiliate_percent'], $y['secondaryaff'], $y['aff_id'], $y['id']));

    

    $f = 0;
    for ($t = 1; $t <= 4; $t++) {
        if ($y['custom_question_' . $t] != '') {
            $z = $db->prepare('INSERT INTO product_questions (product_id, question_index, question) VALUES (?, ?, ?)');
            $z->execute(array($y['id'], $f, $y['custom_question_' . $t]));
            $f++;

            
        }
    }
}*/