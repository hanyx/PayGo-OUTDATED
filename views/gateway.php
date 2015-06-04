<?php

if(!isset($_POST['currency'])  || !isset($_POST['product_price_usd']) || !isset($_POST['to_address'])) {

}

$after_success_url = isset($_POST['after_success_url']) ? $_POST['after_success_url'] : "";

$gp = new GatewayProduct();
$gp->setAfterSuccessUrl($after_success_url);
$gp->setCustom(isset($_POST['custom']) ? $_POST['custom'] : "");
$gp->setFiat($_POST['product_price_usd']);
$gp->setCurrency($_POST['currency']);
$gp->setAddressTo($_POST['to_address']);
$gp->setStatus('Pending');
$gp->setIpnUrl($_POST['ipn_url']);
$gp->create();

include_once('seller/header.php');

switch(true) {
    case $gp->getCurrency() >= 50:
        $cp = new CoinPaymentsAPI();
        $cp->Setup($config['coinpayments']['private'], $config['coinpayments']['public']);

        $currency = '';

        switch($gp->getCurrency()) {
            case ProductCurrency::OMNICOIN:
                $currency = 'OMC';
                break;
            case ProductCurrency::LITECOIN:
                $currency = 'LTC';
                break;
            case ProductCurrency::BITCOIN:
                $currency = 'BTC';
                break;
        }

        $tx = $cp->CreateTransaction(array('buyer_name' => '', 'buyer_email' => '', 'amount' => $gp->getFiat(), 'currency1' => 'USD', 'currency2' => $currency, 'address' => $gp->getAddressTo(), 'custom' => $gp->getCustom(), 'ipn_url' => '', 'quantity' => 1, 'success_url', $gp->getAfterSuccessUrl()));
        if ($tx['error'] != 'ok') {
            die('RELOAD');
        }

        $gp->setProcessorTxid($tx['result']['txn_id']);
        $gp->update();
        break;
    case $gp->getCurrency() == ProductCurrency::PAYPAL:
        ?>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="ppform">
            <input type="hidden" name="cmd" value="_xclick"/>
            <input type="hidden" name="currency_code" value="USD"/>
            <input type="hidden" name="amount" value="<?php echo $gp->getFiat(); ?>" />
            <input type="hidden" name="business" value="<?php echo $gp->getAddressTo(); ?>"/>
            <input type="hidden" name="custom" value="<?php echo $gp->getTxid(); ?>"/>
            <input type="hidden" name="return" value="<?php echo $gp->getAfterSuccessUrl(); ?>"/>
            <input type="hidden" name="notify_url" value="http://payivy.com?txd"/>
            <input type="hidden" name="allow_amount" value="0"/>
            <input type="hidden" name="quantity" value="1"/>
        </form>
        <script>
            document.getElementById("ppform").submit();
        </script>
<?php
        break;

    case $gp->getCurrency() == ProductCurrency::PAYZA:
        ?>
    <form method="post" action="https://secure.payza.com/checkout" id="payza-form">
        <input type="hidden" name="ap_merchant" value="<?php echo $gp->getAddressTo(); ?>" />
        <input type="hidden" name="ap_purchasetype" value="item-goods"/>
        <input type="hidden" name="ap_amount" value="<?php echo $gp->getFiat(); ?>" />
        <input type="hidden" name="ap_itemname" value="PayIvy Gateway - <?php echo $gp->getTxid(); ?>"/>
        <input type="hidden" name="ap_currency" value="USD"/>
        <input type="hidden" name="ap_quantity" value="1"/>
        <input type="hidden" name="ap_returnurl" value="<?php echo $gp->getAfterSuccessUrl();?>"/>
        <input type="hidden" name="apc_1" value="<?php echo $gp->getTxid(); ?>"/>
    </form>
        <script>
            document.getElementById("payza-form").submit();
        </script>
<?php
        break;
}

include_once('seller/footer.php');












