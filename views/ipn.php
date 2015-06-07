<?php
if ($url['1'] == 'paypal') {
    Logger::log('IPN Start: PayPal');
    Logger::log(serialize($_POST));

    if (empty($_POST)) {
        Logger::logAndDie('IPN Fail: Post Empty');
    }

    $ipn = new PayPalIpnCheck();

    $ipn->setSandbox();

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

    if ($order->getQuantity() != $data['quantity']) {
        Logger::logAndDie('IPN Fail: Invalid Quantity');
        die();
    }

    $coupon = new Coupon();
    $coupon->read($order->getCoupon());

    $percentage = $coupon->getReduction() / 100;

    if (($order->getFiat() * $order->getQuantity()) - (($order->getFiat() * $order->getQuantity()) * $percentage) != $data['mc_gross']) {
        Logger::logAndDie('IPN Fail: Invalid Amount');
    }

    if ($data['mc_currency'] != 'USD') {
        Logger::logAndDie('IPN Fail: Invalid Currency');
    }

    if (!strcasecmp($data['business'], $order->getMerchant())) {
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

    if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($config['coinpayments']['merchant-id'])) {
        Logger::logAndDie('IPN Fail: Invalid Merchant ID');
    }

    if ($_SERVER['HTTP_HMAC'] != hash_hmac("sha512", $request, trim($config['coinpayments']['ipn-secret']))) {
        Logger::logAndDie('IPN Fail: Invalid IPN Secret');
    }

    $order = new Order();

    if (!$order->readByTxid($_POST['custom'])) {
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

} else if($url['1'] == 'payza'){
    Logger::log('IPN Start: Payza');
    Logger::log(serialize($_POST));

    define("IPN_V2_HANDLER", "https://secure.payza.com/ipn2.ashx");
    define("TOKEN_IDENTIFIER", "token=");

    $token = urlencode($_POST['token']);
    $token = TOKEN_IDENTIFIER.$token;

    $response = '';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, IPN_V2_HANDLER);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    if(strlen($response) <= 0) {
        Logger::logAndDie('IPN Fail: Invalid Response');
    }

    if(urldecode($response) == "INVALID TOKEN"){
        Logger::logAndDie('IPN Fail: Invalid Token');
    }

    $response = urldecode($response);

    $aps = explode("&", $response);

    $info = array();

    foreach ($aps as $ap)
    {
        $ele = explode("=", $ap);
        $info[$ele[0]] = $ele[1];
    }

    if($info['ap_status'] != "Success" || $info['ap_transactionstate'] != "Completed"){
        Logger::logAndDie('IPN Fail: Invalid Transaction Status');
    }

    if($info['ap_purchasetype'] != 'item-goods'){
        Logger::logAndDie('IPN Fail: Invalid Purchase Type');
    }

    if($info['ap_test'] != 0){
        Logger::logAndDie('IPN Fail: Invalid AP Test');
    }

    if($info['apc_1'] == ''){
        Logger::logAndDie('IPN Fail: Invalid APC');
    }

    if($info['ap_discountamount'] != '0.00'){
        Logger::logAndDie('IPN Fail: Invalid Discount');
    }

    if($info['ap_currency'] != 'USD'){
        Logger::logAndDie('IPN Fail: Invalid Currency');
    }

    $order_txn = $info['apc_1'];

    $order = new Order();

    if(!$order->readByTxid($order_txn)){
        Logger::logAndDie('IPN Fail: No Order Found');
    }

    Logger::log('IPN TXID: ' . $order->getTxid());

    if ($order->isCompleted()) {
        Logger::logAndDie('IPN Fail: Order Already Complete');
    }

    $order->setProcessorTxid($info['ap_referencenumber']);

    if($order->getMerchant() != $info['ap_merchant']){
        Logger::logAndDie('IPN Fail: Invalid Merchant');
    }

    if($order->getQuantity() != $info['ap_quantity']){
        Logger::logAndDie('IPN Fail: Invalid Quantity');
    }

    $coupon = new Coupon();
    $coupon->read($order->getCoupon());

    $percentage = $coupon->getReduction() / 100;

    if(($order->getFiat() * $order->getQuantity()) - ($order->getFiat() * $order->getFiat() * $percentage) != $info['ap_amount']){
        Logger::logAndDie('IPN Fail: Invalid Amount');
    }

    $order->process();
    $order->setCompleted(true);
    $order->update();

    Logger::log('IPN End: Order Complete');
}