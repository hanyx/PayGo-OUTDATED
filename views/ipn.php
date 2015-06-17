<?php
if ($url['1'] == 'paypal') {
    Logger::log('IPN Start: PayPal');
    Logger::log(serialize($_POST));

    if (empty($_POST)) {
        Logger::logAndDie('IPN Fail: Post Empty');
    }

    $ipn = new PayPalIpnCheck();

    if (!$ipn->verify()) {
        Logger::logAndDie('IPN Fail: Verify Fail');
    }

    $data = $ipn->getData(true);

    $order = new Order();

    if (!$order->readByTxid($data['custom'], false)) {
        Logger::logAndDie('IPN Fail: No Order Found');
    }

    Logger::log('IPN TXID: ' . $order->getTxid());

    if ($order->isCompleted()) {
        Logger::logAndDie('IPN Fail: Order Already Completed');
    }

    if ($order->getCurrency() == ProductCurrency::PAYPAL) {
        if ($data['payment_status'] != "Completed") {
            Logger::logAndDie('IPN Fail: Payment Not Completed');
        }
    } else if ($order->getCurrency() == ProductCurrency::PAYPALSUB) {
        if ($data['txn_type'] == "subscr_signup") {
            Logger::logAndDie('IPN Fail: No Subscription Signup');
        }

        if ($data['txn_type'] != "subscr_payment" && $data['txn_type'] != "subscr_signup") {
            Logger::logAndDie('IPN Fail: No Subscription Payment');
        }
    }

    if ($data['quantity'] != 1) {
        Logger::logAndDie('IPN Fail: Invalid Quantity');
        die();
    }

    if (($order->calculateFiatWithCoupon() * $order->getQuantity()) != $data['mc_gross']) {
        Logger::logAndDie('IPN Fail: Invalid Amount');
    }

    if ($data['mc_currency'] != 'USD') {
        Logger::logAndDie('IPN Fail: Invalid Currency');
    }

    if (strcasecmp($data['business'], $order->getMerchant()) !== 0) {
        Logger::logAndDie('IPN Fail: Invalid Merchant');
    }

    $order->setProcessorTxid($data['txn_id']);
    $order->setNative($data['mc_gross']);

    $order->process();
    $order->setCompleted(true);
    $order->update();

    Logger::log('IPN End: Order Complete');

} else if ($url['1'] == 'coinpayments') {
    Logger::log('IPN Start: CoinPayments');
    Logger::log(serialize($_POST));

    if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
        Logger::logAndDie('IPN Fail: Invalid Authentication Mode');
    }

    if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
        Logger::logAndDie('IPN Fail: Bad HMAC');
    }

    $request = file_get_contents('php://input');
    if ($request === FALSE || empty($request)) {
        Logger::logAndDie('IPN Fail: Empty Post');
    }

    if (!isset($_POST['merchant']) || $_POST['merchant'] != $config['coinpayments']['merchant-id']) {
        Logger::logAndDie('IPN Fail: Invalid Merchant ID');
    }

    if ($_SERVER['HTTP_HMAC'] != hash_hmac("sha512", $request, $config['coinpayments']['ipn-secret'])) {
        Logger::logAndDie('IPN Fail: Invalid IPN Secret');
    }

    $order = new Order();

    if (!$order->readByTxid($_POST['custom'], false)) {
        Logger::logAndDie('IPN Fail: No Order Found');
    }

    Logger::log('IPN TXID: ' . $order->getTxid());

    if ($order->isCompleted()) {
        Logger::logAndDie('IPN Fail: Order Already Completed');
    }

    if($order->getProcessorTxid() != $_POST['txn_id']){
        Logger::logAndDie('IPN Fail: Invalid Processor TXID');
    }

    if (!($_POST['status'] >= 100 || ($_POST['status'] >= 0 && $_POST['received_amount'] >= $_POST['amount2']))) {
        Logger::logAndDie('IPN Fail: Invalid Payment Status');
    }

    $order->process();
    $order->setCompleted(true);
    $order->update();

    Logger::log('IPN End: Order Complete');
}

