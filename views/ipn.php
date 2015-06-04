<?php
if ($url['1'] == 'paypal') {

    $ipn = new PayPalIpnCheck();

    if (!$ipn->verify()) {
        die();
    }

    $data = $ipn->getData(true);

    if ($order->getCurrency() == ProductCurrency::PAYPAL) {
        if ($data['payment_status'] != "Completed") {
            die();
        }
    } else if ($order->getCurrency() == ProductCurrency::PAYPALSUB) {
        if ($data['txn_type'] == "subscr_signup") {
            die();
        }

        if ($data['txn_type'] != "subscr_payment" && $data['txn_type'] != "subscr_signup") {
            die();
        }
    }

    $order = new Order();

    if (!$order->readByTxid($data['custom'])) {
        die();
    }

    if ($order->getQuantity() != $data['quantity']) {
        die();
    }

    if ($order->getFiat() * $order->getQuantity() != $data['mc_gross']) {
        die();
    }

    if ($data['mc_currency'] != 'USD') {
        die();
    }

    if ($data['business'] != $order->getMerchant()) {
        die();
    }

    $order->process();

} else if ($url['1'] == 'coinpayments') {

    if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
        die();
    }

    if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
        die();
    }

    $request = file_get_contents('php://input');
    if ($request === FALSE || empty($request)) {
        die();
    }

    if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($config['coinpayments']['merchant-id'])) {
        die();
    }

    if ($_SERVER['HTTP_HMAC'] != hash_hmac("sha512", $request, trim($config['coinpayments']['ipn-secret']))) {
        die();
    }

    $order = new Order();

    if (!$order->readByTxid($_POST['custom'])) {
        die();
    }

    if (!($_POST['status'] >= 100 || ($_POST['status'] >= 0 && $_POST['received_amount'] >= $_POST['amount2']))) {
        die();
    }

    $order->process();

}