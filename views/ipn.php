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

    $coupon = new ProductCoupon();
    $coupon->read($order->getCoupon());

    $percentage = $coupon->getReduction() / 100;


    if (($order->getFiat() * $order->getQuantity()) - (($order->getFiat() *$order->getQuantity()) * $percentage) != $data['mc_gross']) {
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

    if($order->getProcessorTxid() != $_POST['txn_id']){
        die();
    }

    if (!($_POST['status'] >= 100 || ($_POST['status'] >= 0 && $_POST['received_amount'] >= $_POST['amount2']))) {
        die();
    }

    $order->process();
} else if($url['1'] == 'payza'){
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

    if(strlen($response) > 0){
        if(urldecode($response) == "INVALID TOKEN"){

        } else {
            $response = urldecode($response);

            $aps = explode("&", $response);

            $info = array();

            foreach ($aps as $ap)
            {
                $ele = explode("=", $ap);
                $info[$ele[0]] = $ele[1];
            }

            if($info['ap_status'] != "Success" || $info['ap_transactionstate'] != "Completed"){
                die();
            }

            if($info['ap_purchasetype'] != 'item-goods'){
                die();
            }

            if($info['ap_test'] != 0){
                die();
            }

            if($info['apc_1'] == ''){
                die();
            }

            if($info['ap_discountamount'] != '0.00'){
                die();
            }

            if($info['ap_currency'] != 'USD'){
                die();
            }

            $order_txn = $info['apc_1'];

            $order = new Order();
            if(!$order->readByTxid($order_txn)){
                die();
            }

            $order->setProcessorTxid($info['ap_referencenumber']);

            if($order->getMerchant() != $info['ap_merchant']){
                die();
            }

            if($order->getQuantity() != $info['ap_quantity']){
                die();
            }

            $coupon = new ProductCoupon();
            $coupon->read($order->getCoupon());

            $percentage = $coupon->getReduction() / 100;

            if(($order->getFiat() * $order->getQuantity()) - ($order->getFiat() * $order->getFiat() * $percentage) != $info['ap_amount']){
                die();
            }

            $order->process();
        }
    }
}