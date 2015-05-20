<?php
$product = new Product();

if (!$product->readByUrl($url[1])) {
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

                    if (!$product->acceptsCurrency($_POST['currency'])) {
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

                    $qs = count($product->getQuestions());

                    for ($x = 1; $x <= $qs; $x++) {
                        if (isset($_POST['q-' . $x])) {
                            $questions[] = htmlspecialchars($_POST['q-' . $x], ENT_QUOTES);
                        }
                    }

                    if (count($questions) != $qs) {
                        $errorMessage = 'RELOAD';
                        break;
                    }

                    $order = new Order();

                    $order->setProductId($product->getId());
                    $order->setQuantity((int)$_POST['quantity']);
                    $order->setCurrency($_POST['currency']);
                    $order->setFiat($_POST['quantity'] * $product->getPrice());
                    $order->setEmail($_POST['email']);
                    $order->setIp(getRealIp());
                    $order->setQuestions($questions);

                    $order->create();

                    if ($order->getCurrency() == ProductCurrency::PAYPAL || $order->getCurrency() == ProductCurrency::PAYPALSUB) {
                        $response['action'] = 'pp-checkout';
                        $response['data'] = array('sub' => $order->getCurrency() == ProductCurrency::PAYPALSUB, 'business' => $seller->getPaypal(), 'itemname' => $product->getTitle(), 'itemnumber' => $product->getId(), 'amount' => $order->getFiat(), 'custom' => $order->getTxid(), 'shipping' => $product->getRequireShipping(), 'quantity' => $order->getQuantity(), 'sub-length' => $product->getPaypalSubLength(), 'sub-unit' => $product->getPaypalSubUnit());
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

                        $tx = $cp->CreateTransaction(array('buyer_name' => '', 'buyer_email' => $_POST['email'], 'amount' => $order->getFiat(), 'currency1' => 'USD', 'currency2' => $currency, 'address' => $address, 'item_name' => $product->getTitle(), 'item_number' => $product->getId(), 'custom' => $order->getTxid(), 'ipn_url' => '', 'quantity' => $order->getQuantity()));

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

                    if ($order->readByTxid($_POST['txid'])) {

                        if ($order->getCurrency() == ProductCurrency::BITCOIN || $order->getCurrency() == ProductCurrency::LITECOIN || $order->getCurrency() == ProductCurrency::OMNICOIN) {
                            $cp = new CoinPaymentsAPI();
                            $cp->Setup($config['coinpayments']['private'], $config['coinpayments']['public']);

                            $tx = $cp->GetTransactionInfo($order->getProcessorTxid());

                            if ($tx['error'] == 'ok') {
                                $response = array('title' => $product->getTitle(), 'totalPrice' => $order->getQuantity() * $order->getFiat(), 'txid' => $order->getTxid(), 'quantity' => $order->getQuantity(), 'price' => $order->getFiat(), 'created' => $tx['result']['time_created'], 'expires' => $tx['result']['time_expires'], 'status' => $tx['result']['status'], 'coin' => $tx['result']['coin'], 'amount' => $tx['result']['amountf'], 'received' => $tx['result']['receivedf'], 'confirms' => $tx['result']['recv_confirms'], 'address' => $tx['result']['payment_address']);
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

foreach ($product->getCurrency() as $currency) {
    switch ($currency) {
        case 0:
            if ($seller->getPaypal() == '') {
                die('invalid payment setup 0');
            }
            break;
        case 1:
            if ($seller->getBitcoin() == '') {
                die('invalid payment setup 1');
            }
            break;
        case 2:
            if ($seller->getLitecoin() == '') {
                die('invalid payment setup 2');
            }
            break;
        case 3:
            if ($seller->getOmnicoin() == '') {
                die('invalid payment setup 3');
            }
            break;
    }
}

include_once('seller/header.php');
?>
<style>
    .btn-success {
        background-color: #91a7c0 !IMPORTANT;
    }
</style>
<div style='max-width: 700px; margin: 70px auto 0 auto;'>
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
                            <button class='btn btn-success pull-right' style='margin-top: 3px;' data-toggle='modal' data-target='#contact'>Contact Seller <span class='fa fa-envelope'></span></button>
                            <span class='thumb-small avatar pull-right' style='margin: 0 7px; width: 43px;'>
                               <img class='img-circle' src='/images/avatar.png'>
                            </span>
                            <div class='pull-right text-right' style='font-size: 15px;'>
                                <b><?php echo $seller->getUsername(); ?></b>
                                <br>
                                <span style='color:#AAAAAA;'>Premium Seller</span>
                            </div>
                        </div>
                        <div class='col-lg-6 visible-xs' style='font-size: 15px; margin-top: 3px; clear: both;'>
                            <b>Quantity Remaining</b>
                            <div class='btn btn-success'>4</div> </div>
                        <div class='col-lg-12 hidden-xs'>
                            <button class='btn btn-success pull-right' style='margin-top: 3px;' data-toggle='modal' data-target='#contact'>Contact Seller <span class='fa fa-envelope'></span></button>
                            <span class='thumb-small avatar pull-right' style='margin: 0 7px; width: 43px;'>
                                <img class='img-circle' src='/images/avatar.png'>
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
                                <h5 class='semibold mt0 mb5'>abe</h5>
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
                            if ($product->acceptsCurrency(ProductCurrency::PAYPAL)) {
                                echo '<button onclick=\'pay(0);\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'>PayPal</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::PAYPALSUB)) {
                                echo '<button onclick=\'pay(1);\' class=\'btn btn-success\' style=\'width: 205px; font-size: 18px; margin: 0 5px 5px 0;\'>PayPal Subscription</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::BITCOIN)) {
                                echo '<button onclick=\'pay(2);\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 20px; height: 18px; background-image: url("/images/crypto-icons.png"); background-position: -3px -20px; vertical-align: -2px;\'></span>Bitcoin</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::LITECOIN)) {
                                echo '<button onclick=\'pay(4);\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 20px; height: 19px; background-image: url("/images/crypto-icons.png"); background-position: -3px -78px; vertical-align: -2px;\'></span>Litecoin</button>';
                            }
                            if ($product->acceptsCurrency(ProductCurrency::OMNICOIN)) {
                                echo '<button onclick=\'pay(5);\' class=\'btn btn-success\' style=\'width: 155px; font-size: 18px; margin: 0 5px 5px 0;\'><span style=\'display: inline-block; width: 23px; height: 22px; background-image: url("/images/crypto-icons.png"); background-position: -3px -137px; vertical-align: -4px;\'></span>Omnicoin</button>';
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
    </div>
</div>
<script>
    var apiEndpoint = '<?php echo $product->getUrl(); ?>/buy';
    var currency = '0';

    $('#quantity').change(function() {
        if ($(this).val() < 1) {
            $(this).val('1');
        }

        $('.total').html('$' + ((($(this).val() * <?php echo $product->getPrice(); ?>) * 100) / 100).toFixed(2));
    });

    function pay(type) {
        currency = type;
        $('.step-1').slideUp(1000, function() {
            setTimeout(function() { $('.step-2').slideDown(1000); }, 1000);
        });
    }

    function checkout() {
        var data = {'action': 'purchase', 'price': <?php echo $product->getPrice(); ?>, 'currency': currency, 'email': $('#email').val(), 'quantity': $('#quantity').val()};

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
                            <input type=\'hidden\' name=\'notify_url\' value=\'\'>\
                            <input type=\'hidden\' name=\'return\' value=\'\'>\
                            <input type=\'hidden\' name=\'cancel_return\' value=\'\'>\
                            <input type=\'hidden\' name=\'invoice\' value=\'\'>\
                            <input type=\'hidden\' name=\'allow_amount\' value=\'0\'>\
                            <input type=\'hidden\' name=\'want_shipping\' value=\'' + data.response.data.shipping + '\'>\
                            <input type=\'hidden\' name=\'quantity\' value=\'' + data.response.data.quantity + '\'>\
                            <input type=\'hidden\' name=\'no_shipping\' value=\'' + (data.response.data.shipping ? '0' : '1') + '\'>\
                            ' + (data.response.data.sub ? '<input name=\'a3\' type=\'hidden\' value=\'' + data.response.data.amount + '\'><input name=\'t3\' type=\'hidden\' value=\'' + data.response.data.sublength + '\'><input name=\'p3\' type=\'hidden\' value=\'' + data.response.data.subunit + '\'><input name=\'src\' type=\hidden\' value=\'1\'>' : '') + '\
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
                                    $('.step-3 .panel-body').html("<p>Please send <code>" + data.response.amount + " " + data.response.coin + "</code> to <code>" + data.response.address + "</code> in the next <b>" + Math.floor((data.response.expires - ((new Date).getTime() / 1000)) / 60) + "</b> minutes.</p><p><b>Transaction ID:</b> " + data.response.txid + "<br /><b>Product:</b> " + data.response.title + "<br /><b>Price:</b> " + data.response.price + " USD<br /><b>Quantity:</b> " + data.response.quantity + "<br /><b>Total Price:</b> " + data.response.totalPrice + " USD<br /><b>Total Received:</b> " + data.response.received + " " + data.response.coin + "<br /><b>Total Left:</b> " + (data.response.amount - data.response.received) + " " + data.response.coin + "</p><p style='text-align: center; font-size: 30px;'><i class='fa fa-spinner fa-spin'></i> Awaiting Payment</p><p><i>After payment is sent, please wait 2-15 minutes for your product to be delivered through email. If you have any issues, please contact us with your transaction ID at <a href='http://support.payivy.com/'>support.payivy.com</a></i></p><p style='text-align: center; font-size: 12px;'>Powered by <a href='https://coinpayments.net'>CoinPayments</a></p>");
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
include_once('seller/footer.php');