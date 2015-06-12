<?php
$product = new Product();

if (!$product->readByUrl($url[1]) && !$product->readByUrlTitle(urldecode($url[1]))) {
    include_once('404.php');
    die();
}

$seller = new User();
$seller->read($product->getSellerId());

$product = Product::getProduct($product->getId());

if (count($url) == 3 && $url[2] == 'buy') {
    if (isset($_POST['action'])) {
        $response = array();
        $errorMessage = '';

        $action = $_POST['action'];

        switch ($action) {
            case 'purchase':
                if (isset($_POST['price']) && isset($_POST['currency']) && isset($_POST['email']) && isset($_POST['quantity'])) {
                    if ($_POST['price'] != $product->getPrice()) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    if (!$product->acceptsCurrency((int)$_POST['currency'])) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    if ($_POST['quantity'] < 1 || !ctype_digit($_POST['quantity'])) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    if ($product->getType() == ProductType::SERIAL && count($product->getSerials()) < $_POST['quantity']) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    $questions = array();

                    $qs = $product->getQuestions();

                    for ($x = 1; $x <= count($qs); $x++) {
                        if (isset($_POST['q-' . $x])) {
                            $questions[] = array($qs[$x - 1], htmlspecialchars($_POST['q-' . $x], ENT_QUOTES));
                        }
                    }

                    if (count($questions) != count($qs)) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    $order = new Order();

                    $order->setProductId($product->getId());
                    $order->setQuantity((int)$_POST['quantity']);
                    $order->setCurrency($_POST['currency']);
                    $order->setFiat($product->getPrice());
                    $order->setEmail($_POST['email']);
                    $order->setIp(getRealIp());
                    $order->setQuestions($questions);


                    if ($_POST['couponCode'] != '') {
                        $coupon = new Coupon();

                        if (!$coupon->readByNameAndSellerId($_POST['couponCode'], $seller->getId()) || $coupon->getUsedAmount() >= $coupon->getMaxUsedAmount()) {
                            $errorMessage = 'RELOAD';
                            break;
                        } else {
                            $order->setCouponUsed(true);
                            $order->setCouponName($coupon->getName());
                            $order->setCouponReduction($coupon->getReduction());
                        }
                    }

                    if ($_POST['affiliate'] != '') {
                        $affiliate = new Affiliate();

                        if (!$affiliate->read($_POST['affiliate']) || $affiliate->getProductId() != $seller->getId()) {
                            $errorMessage = 'RELOAD';
                            break;
                        } else {
                            $order->setAffiliateUsed(true);
                            $order->setAffiliate($affiliate->getId());
                        }
                    }

                    $order->setSuccessUrl($product->getSuccessUrl());

                    $order->create();

                    if ($order->getCurrency() == ProductCurrency::PAYPAL || $order->getCurrency() == ProductCurrency::PAYPALSUB) {
                        $response['action'] = 'pp-checkout';
                        $response['data'] = array('sub' => $order->getCurrency() == ProductCurrency::PAYPALSUB, 'business' => $seller->getPaypal(), 'itemname' => $product->getTitle(), 'itemnumber' => $product->getId(), 'amount' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'custom' => $order->getTxid(), 'shipping' => $product->getRequireShipping(), 'quantity' => 1, 'sub-length' => $product->getPaypalSubLength(), 'sub-unit' => $product->getPaypalSubUnit(), 'success_url' => $product->getSuccessUrl());

                        $order->setMerchant($seller->getPaypal());
                        $order->update();
                    } else if ($order->getCurrency() == ProductCurrency::BITCOIN || $order->getCurrency() == ProductCurrency::LITECOIN|| $order->getCurrency() == ProductCurrency::OMNICOIN) {
                        $cp = new CoinPaymentsAPI();
                        $cp->Setup($config['coinpayments']['private'], $config['coinpayments']['public']);

                        $currency = '';
                        $address = '';

                        switch ($order->getCurrency()) {
                            case ProductCurrency::BITCOIN:
                                $currency = 'BTC';
                                $address = $seller->getBitcoin();
                                break;
                            case ProductCurrency::LITECOIN:
                                $currency = 'LTC';
                                $address = $seller->getLitecoin();
                                break;
                            case ProductCurrency::OMNICOIN:
                                $currency = 'OMC';
                                $address = $seller->getOmnicoin();
                                break;
                        }

                        $tx = $cp->CreateTransaction(array('buyer_name' => '', 'buyer_email' => '', 'amount' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'currency1' => 'USD', 'currency2' => $currency, 'address' => $address, 'item_name' => $product->getTitle(), 'item_number' => $product->getId(), 'custom' => $order->getTxid(), 'ipn_url' => $config['url']['protocol'] . $config['url']['domain'] . '/ipn/coinpayments/', 'quantity' => 1, 'success_url' => $product->getSuccessUrl()));

                        if ($tx['error'] != 'ok') {
                            $errorMessage = 'RELOAD';
                            break;
                        }

                        $order->setProcessorTxid($tx['result']['txn_id']);

                        $order->update();

                        $response['action'] = 'display-crypto';
                        $response['data'] = array('txid' => $order->getTxid());
                    }

                } else {
                    $errorMessage = 'RELOAD';
                    break;
                }
                break;
            case 'checktx':
                if (isset($_POST['txid'])) {
                    $order = new Order();

                    if ($order->readByTxid($_POST['txid'], false)) {

                        if ($order->getCurrency() == ProductCurrency::BITCOIN || $order->getCurrency() == ProductCurrency::LITECOIN || $order->getCurrency() == ProductCurrency::OMNICOIN) {
                            $cp = new CoinPaymentsAPI();
                            $cp->Setup($config['coinpayments']['private'], $config['coinpayments']['public']);

                            $tx = $cp->GetTransactionInfo($order->getProcessorTxid());

                            if ($tx['error'] == 'ok') {
                                $response = array('title' => $product->getTitle(), 'txid' => $order->getTxid(), 'price' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'created' => $tx['result']['time_created'], 'expires' => $tx['result']['time_expires'], 'status' => $tx['result']['status'], 'coin' => $tx['result']['coin'], 'amount' => $tx['result']['amountf'], 'received' => $tx['result']['receivedf'], 'confirms' => $tx['result']['recv_confirms'], 'address' => $tx['result']['payment_address'], 'success_url' => $product->getSuccessUrl());
                            }
                        } else {
                            $errorMessage = 'RELOAD';
                            break;
                        }
                    } else {
                        $errorMessage = 'RELOAD';
                        break;
                    }
                } else {
                    $errorMessage = 'RELOAD';
                    break;
                }
                break;
        }

        if ($errorMessage != '') {
            die(json_encode(array('error' => true, 'error-message' => $errorMessage)));
        } else {
            die(json_encode(array('error' => false, 'response' => $response)));
        }
    }
}

if(isset($_GET['redeemcoupon']) && $_GET['redeemcoupon'] == "true" && isset($_GET['couponcode']) && ctype_alnum($_GET['couponcode'])) {
    $coupon = new Coupon();

    if ($coupon->readByNameAndSellerId($_GET['couponcode'], $seller->getId())) {
        if ($coupon->getUsedAmount() >= $coupon->getMaxUsedAmount()) {
            die('used');
        } else {
            die($coupon->getName());
        }
    } else {
        die('false');
    }
}

if (isset($_POST['contact-seller']) && isset($_POST['email']) && isset($_POST['name']) && isset($_POST['message'])) {
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $uas->addMessage(new ErrorSuccessMessage('Invalid Email'));
    } else {
        $message = new Message();

        $message->setSender($_POST['email']);
        $message->setRecipient($seller->getId());
        $message->setMessage(htmlspecialchars($_POST['message'], ENT_QUOTES));

        $message->send();

        $uas->addMessage(new ErrorSuccessMessage('Message sent!', false));
    }
}

$view = new View();

$view->setProductId($product->getId());
$view->setIp(getRealIp());

if (isset($_SERVER['HTTP_REFERER'])) {
    $view->setReferrer(htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES));
}

$view->create();

$affiliate = false;

if (count($url) == 4 && $url[2] == 'a') {
    $affiliate = strcasecmp($url[3], $product->getAffiliateId()) == 0;
}

if ($affiliate && isset($_POST['affiliate-register']) && isset($_POST['email'])) {
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $uas->addMessage(new ErrorSuccessMessage('Invalid Email'));
    } else {
        $aff = new Affiliate();

        if ($aff->readByEmailProductId($_POST['email'], $product->getId())) {
            $uas->addMessage(new ErrorSuccessMessage('Email is already registered as an affiliate for this product'));
        } else {
            $password = generateRandomString(10);

            $aff = new Affiliate();

            $aff->setEmail($_POST['email']);
            $aff->setProductId($product->getId());
            $aff->setPassword($password);

            $aff->create();

            $mailer = new Mailer();

            $mailer->sendTemplate(EmailTemplate::AFFILIATEREGISTER, $_POST['email'], '', $product->getUrl() . '/' . $aff->getId(), $password);

            $uas->addMessage(new ErrorSuccessMessage('Affiliate Registered. Please check your email for more information', false));
        }
    }
}

__header($product->getTitle());
?>
<style>
    .btn-success {
        background-color: #91a7c0 !IMPORTANT;
    }
</style>
<div style='max-width: 700px; margin: 70px auto 0 auto;'>
<?php
if ($uas->hasMessage()) {
    $uas->printMessages();
}
?>
    <div class='row'>
        <div class='col-lg-12'>
            <div class='step-1'>
                <section class='panel'>
                    <div class='panel panel-default text-center' style='margin: 10px;'>
                        <div class='panel-heading' style='cursor: pointer; background-color: #91a7c0;'>
                            <h4 class='panel-title' data-toggle='collapse' href='#collapse'>
                                <a style='color: white; font-weight: bold;'>
                                    <?php echo $product->getTitle(); ?> <span class='fa fa-angle-down pull-right'></span>
                                </a>
                            </h4>
                        </div>
                        <div style='height: auto;' class='panel-collapse collapse in' id='collapse'>
                            <div style='margin: 15px; overflow:hidden;'>
                                <?php echo $product->getDescription(); ?>
                            </div>
                        </div>
                    </div>
                </section>
                <section class='panel'>
                    <div class='row' style='margin: 20px 0px;'>
                        <div class='col-lg-6 visible-xs' style='margin-bottom: 50px;'>
                            <button class='btn btn-success pull-right' style='margin-top: 3px;' data-toggle='modal' data-target='#contact-seller'>Contact Seller <span class='fa fa-envelope'></span></button>
                            <span class='thumb-small avatar pull-right' style='margin: 0 7px; width: 43px;'>
                               <img class='img-circle' src='/images/avatar.png' style="width: 43px;">
                            </span>
                            <div class='pull-right text-right' style='font-size: 15px;'>
                                <b><?php echo $seller->getUsername(); ?></b>
                                <br>
                                <span style='color:#AAAAAA;'>Premium Seller</span>
                            </div>
                        </div>
                        <div class='col-lg-6 visible-xs' style='font-size: 15px; margin-top: 3px; clear: both;'>
                            <b>Quantity Remaining</b>
                            <div class='btn btn-success'>4</div>
                        </div>
                        <div class='col-lg-12 hidden-xs'>
                            <button class='btn btn-success pull-right' style='margin-top: 3px;' data-toggle='modal' data-target='#contact-seller'>Contact Seller <span class='fa fa-envelope'></span></button>
                            <span class='thumb-small avatar pull-right' style='margin: 0 7px; width: 43px;'>
                                <img class='img-circle' src='/images/avatar.png' style="width: 43px;">
                            </span>
                            <div class='pull-right text-right' style='font-size: 15px;'>
                                <b><?php echo $seller->getUsername(); ?></b>
                                <br>
                                <span style='color:#AAAAAA;'>Premium Seller</span>
                            </div>
                            <b>Quantity Remaining</b>
                            <div class='btn btn-success'><?php echo $product->getType() == ProductType::SERIAL ? count($product->getSerials()) : '∞'; ?></div></div>
                    </div>
                </section>
                <section class='panel table-responsive'>
                    <table class='table'>
                        <thead style='font-size: 15px;'>
                        <tr>
                            <th style='padding: 15px 15px;'>Description</th>
                            <th class='text-center' style='padding: 15px 15px;'>Quantity</th>
                            <th class='text-center' style='padding: 15px 15px;'>Price</th>
                            <th class='text-center' style='padding: 15px 15px;'>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <h5 class='semibold mt0 mb5'><?php echo $product->getTitle(); ?></h5>
                            </td>
                            <td class='valign-top text-center'><input min='1' max='<?php echo $product->getType() == ProductType::SERIAL ? count($product->getSerials()) : '100'; ?>' style='width: 60px;' value='1' id='quantity' type='number'></td>
                            <td class='valign-top text-center'><span class='bold'>$<?php echo $product->getPrice(); ?></span></td>
                            <td class='valign-top text-center btn-success' rowspan='2' style='font-weight: bold; font-size: 25px; vertical-align: middle;'><span class='bold total'>$<?php echo $product->getPrice(); ?></span></td>
                        </tr>
                        <tr>
                            <td>
                                <h5 class='semibold mt0 mb5'>PayIvy Gateway</h5>
                                <span class='text-muted'>Instant delivery after payment.</span>
                            </td>
                            <td class='valign-top text-center'><span class='bold'></span></td>
                            <td class='valign-top text-center'><span class='bold'>$0.00</span></td>
                        </tr>
                        </tbody>
                    </table>
                </section>
                <section class='panel'>
                    <div class='row' style='margin: 20px 0px;'>
                        <div class='col-lg-3' style='font-size: 15px; margin-top: 3px;'>
                            <b>Pay Now</b>
                            <br>
                            <span style='color:#AAAAAA;'>Choose payment option</span>
                        </div>
                        <div class='col-lg-9'>
                            <?php
                            if ($product->acceptsCurrency(ProductCurrency::PAYPAL) && $seller->getPaypal() != '') {
                                echo '<button onclick=\'pay(' . ProductCurrency::PAYPAL . ');\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'>PayPal</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::PAYPALSUB) && $seller->getPaypal() != '') {
                                echo '<button onclick=\'pay(' . ProductCurrency::PAYPALSUB . ');\' class=\'btn btn-success\' style=\'width: 205px; font-size: 18px; margin: 0 5px 5px 0;\'>PayPal Subscription</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::BITCOIN) && $seller->getBitcoin() != '') {
                                echo '<button onclick=\'pay(' .ProductCurrency::BITCOIN . ');\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 20px; height: 18px; background-image: url("/images/crypto-icons.png"); background-position: -3px -20px; vertical-align: -2px;\'></span>Bitcoin</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::LITECOIN) && $seller->getLitecoin() != '') {
                                echo '<button onclick=\'pay(' . ProductCurrency::LITECOIN . ');\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 20px; height: 19px; background-image: url("/images/crypto-icons.png"); background-position: -3px -78px; vertical-align: -2px;\'></span>Litecoin</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::OMNICOIN) && $seller->getOmnicoin() != '') {
                                echo '<button onclick=\'pay(' . ProductCurrency::OMNICOIN . ');\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 23px; height: 22px; background-image: url("/images/crypto-icons.png"); background-position: -3px -137px; vertical-align: -4px;\'></span>Omnicoin</button>';
                            }
                            ?>
                        </div>
                    </div>
                </section>

        </div>
            <div class='step-2' style='display:none;'>
                <div class='panel panel-default'>
                    <div class='panel-heading' style='background-color: #91a7c0;'>
                        <h4 class='panel-title text-center'>
                            <a style='color: white; font-weight: bold;'>
                                Checkout
                            </a>
                        </h4>
                    </div>
                    <div class='panel-body'>
                        <p>Your Email:</p>
                        <input type='text' id='email' class='form-control' required>
                        <br>
                        <?php
                        $x = 0;
                        foreach ($product->getQuestions() as $question) {
                            $x++;
                            ?>
                            <p><?php echo $question; ?>:</p>
                            <input type='text' id='custom-question-<?php echo $x; ?>' class='form-control' required>
                            <br>
                        <?php
                        }
                        ?>

                            <p>Coupon:</p>
                            <div class="input-group">
                            <input type="text" id="couponCode" class="form-control"/>
                                <span class="input-group-btn">
        <button class="btn btn-default" onclick="redeemCoupon();" type="button">Apply</button>
      </span>
                            </div>
                            <div class="form-goup">
                                <label>Applied coupon: </label><span id="coupon"> None</span>
                            </div>
                            <input type="hidden" value="" id="couponCodeResult" name="couponCode"/>
                            <br>

                        <input type='button' onClick='checkout()' class='btn btn-success' value='Continue to Payment'>
                    </div>
                </div>
            </div>
            <div class='step-3' style='display:none;'>
                <div class='panel panel-default'>
                    <div class='panel-heading' style='background-color: #91a7c0;'>
                        <h4 class='panel-title text-center'>
                            <a style='color: white; font-weight: bold;'>
                                Payment
                            </a>
                        </h4>
                    </div>
                    <div class='panel-body'></div>
                </div>
            </div>
    </div>
    <div class="modal fade" id="contact-seller" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Contact Seller</h4>
                </div>
                <div class="modal-body">
                    <form action="" method="post" style="margin-top:10px;">
                        <div class="form-group">
                            <input name="email" type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input name="name" type="text" class="form-control" placeholder="Name" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" id="email_message" class="form-control" placeholder="Message" style="height: 120px;" required></textarea>
                        </div>
                        <button type="submit" name='contact-seller' class="btn btn-flat btn-success">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($affiliate) {
        ?>
        <div class="modal fade" id="affiliate" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Become an Affiliate</h4>
                    </div>
                    <div class="modal-body">
                        <form action="#" method="post">
                            <p>The seller has enabled the affiliate system for this product. By becoming
                                an affiliate you will receive a commission per sale determined by the
                                seller. To signup all you need is a paypal email address. Please note
                                that for each sale, both the seller and the affiliate will be notified,
                                but the payout will be sent manually by the seller and not
                                instantly.</p>

                            <p>The seller has agreed to give
                                affiliates <?php echo $product->getAffiliatePercent(); ?>% of each
                                sale.</p>

                            <div class="form-group">
                                <input name="email" type="email" class="form-control" placeholder="Email Address">
                            </div>

                            <button name='affiliate-register' type="submit" class="btn btn-flat btn-success btn-block">Become an Affiliate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script>
    $('#affiliate').modal('show');

    var apiEndpoint = '<?php echo $product->getUrl(); ?>/buy';
    var currency = '0';

    $('#quantity').change(function() {
        if ($(this).val() < 1) {
            $(this).val('1');
        }

        $('.total').html('$' + ((($(this).val() * <?php echo $product->getPrice(); ?>) * 100) / 100).toFixed(2));
    });

    function redeemCoupon()
    {
        $.get( "?redeemcoupon=true&couponcode=" + $('#couponCode').val(), function( data ) {
            switch (data){
                case "false":
                        alert('The coupon you entered is not valid.');
                    break;
                case "used":
                        alert('The maximum usage limit of this coupon has been reached.');
                    break;
                default:
                    $('#coupon').text(" "+$('#couponCode').val());
                    $('#couponCodeResult').val(data)
                    break;
            }
        });
    }

    function pay(type) {
        currency = type;
        $('.step-1').slideUp(1000, function() {
            setTimeout(function() { $('.step-2').slideDown(1000); }, 1000);
        });
    }

    function checkout() {
        <?php
        $affId = false;

        if (count($url) == 3) {
            $affiliate = new Affiliate();

            if ($affiliate->read($url['2']) && $affiliate->getProductId() == $seller->getId()) {
                $affId = $affiliate->getId();
            }
        }
        ?>
        var data = {'action': 'purchase', 'price': <?php echo $product->getPrice(); ?>, 'currency': currency, 'email': $('#email').val(), 'quantity': $('#quantity').val(), 'couponCode': $('#couponCodeResult').val(), 'success_url': '<?php echo $product->getSuccessUrl(); ?>', 'affiliate': '<?php echo $affId; ?>'};

        for (var x = 1; x <= <?php echo count($product->getQuestions()); ?>; x++) {
            data['q-' + x] = $('#custom-question-' + x).val();
        }

        $.post(apiEndpoint, data, function(data) {
            data = $.parseJSON(data);

            if (data.error) {
                location.reload();
            } else {
                switch (data.response.action) {
                    case 'pp-checkout':
                        $('body').append('<form action=\'https://www.paypal.com/cgi-bin/webscr\' method=\'post\' id=\'ppform\'>\
                        	<input type=\'hidden\' name=\'cmd\' id=\'paycmd\' value=\'' + (data.response.data.sub ? '_xclick-subscriptions' : '_xclick') + '\'>\
                            <input type=\'hidden\' name=\'currency_code\' value=\'USD\'>\
                            <input type=\'hidden\' name=\'business\' value=\'' + data.response.data.business + '\'>\
                            <input type=\'hidden\' name=\'item_name\' value=\'' + data.response.data.itemname + '\'>\
                            <input type=\'hidden\' name=\'item_number\' value=\'' + data.response.data.itemnumber + '\'>\
                            <input type=\'hidden\' name=\'amount\' value=\'' + data.response.data.amount + '\'>\
                            <input type=\'hidden\' name=\'custom\' value=\'' + data.response.data.custom + '\'>\
                            <input type=\'hidden\' name=\'notify_url\' value=\'<?php echo $config['url']['protocol'] . $config['url']['domain'] . '/ipn/paypal'; ?>\'>\
                            <input type=\'hidden\' name=\'return\' value=\'' + data.response.data.success_url +'\'>\
                            <input type=\'hidden\' name=\'cancel_return\' value=\'\'>\
                            <input type=\'hidden\' name=\'invoice\' value=\'\'>\
                            <input type=\'hidden\' name=\'allow_amount\' value=\'0\'>\
                            <input type=\'hidden\' name=\'want_shipping\' value=\'' + data.response.data.shipping + '\'>\
                            <input type=\'hidden\' name=\'quantity\' value=\'' + data.response.data.quantity + '\'>\
                            <input type=\'hidden\' name=\'no_shipping\' value=\'' + (data.response.data.shipping ? '0' : '1') + '\'>\
                            ' + (data.response.data.sub ? '<input name=\'a3\' type=\'hidden\' value=\'' + data.response.data.amount + '\'><input name=\'t3\' type=\'hidden\' value=\'' + data.response.data.sublength + '\'><input name=\'p3\' type=\'hidden\' value=\'' + data.response.data.subunit + '\'><input name=\'src\' type=\'hidden\' value=\'1\'>' : '') + '\
                        </form>');

                        document.getElementById('ppform').submit();
                        break;
                    case 'display-crypto':
                        $('.step-2').slideUp(1000, function() {
                            setTimeout(function() { $('.step-3').slideDown(1000); }, 1000);
                        });

                        function update() {
                            var data2 = {'action': 'checktx', 'txid': data.response.data.txid};

                            $.post(apiEndpoint, data2, function(data) {
                                data = $.parseJSON(data);

                                if (data.response.received >= data.response.amount) {
                                    $('.step-3 .panel-body').html("<p>Transaction Complete. Please check your email for your product. It may take 2-15 minutes for your product to be delivered.</p><p style='text-align: center; font-size: 12px;'>Powered by <a href='https://coinpayments.net'>CoinPayments</a></p>");
                                } else if (((data.response.expires - ((new Date).getTime() / 1000)) / 60) <= 0) {
                                    $('.step-3 .panel-body').html("<p>Transaction has expired.</p><p style='text-align: center; font-size: 12px;'>Powered by <a href='https://coinpayments.net'>CoinPayments</a></p>");
                                } else {
                                    $('.step-3 .panel-body').html("<p>Please send <code>" + data.response.amount + " " + data.response.coin + "</code> to <code>" + data.response.address + "</code> in the next <b>" + Math.floor((data.response.expires - ((new Date).getTime() / 1000)) / 60) + "</b> minutes.</p><p><b>Transaction ID:</b> " + data.response.txid + "<br /><b>Product:</b> " + data.response.title + "<br /><b>Price:</b> " + data.response.price + " USD<br /><b>Total Received:</b> " + data.response.received + " " + data.response.coin + "<br /><b>Total Left:</b> " + (data.response.amount - data.response.received) + " " + data.response.coin + "</p><p style='text-align: center; font-size: 30px;'><i class='fa fa-spinner fa-spin'></i> Awaiting Payment</p><p><i>After payment is sent, please wait 2-15 minutes for your product to be delivered through email. If you have any issues, please contact us with your transaction ID at <a href='http://support.payivy.com/'>support.payivy.com</a></i></p><p style='text-align: center; font-size: 12px;'>Powered by <a href='https://coinpayments.net'>CoinPayments</a></p>");
                                }
                            });
                        }

                        setInterval(update, 15000);

                        update();

                        break;
                }
            }
        });
    }
</script>
<?php
__footer();