<?php
$product = new Product();

if (!$product->readByUrl($url[1]) && !$product->readByUrlTitle(urldecode($url[1]))) {
    include_once('404.php');
    die();
}

if(count($url) == 4 && $url[2] == 'i'){
    $order = new Order();

    if(!$order->read($url[3], false) || $order->getProductId() != $product->getId()){
        include_once('404.php');
        die();
    }
}

$seller = new User();
$seller->read($product->getSellerId());

if (count($url) == 3 && $url[2] != 'i') {
    $affiliate = new Affiliate();

    if ($affiliate->read($url['2']) && $affiliate->getProductId() == $seller->getId() && $product->getAffiliateSecondaryLink() != '') {
        setcookie($product->getId() . '-affid', $affiliate->getId(), time() + 60 * 60 * 24 * 10, '/', $config['url']['domain'], true);

        header('Location: ' . $product->getAffiliateSecondaryLink());
    }
}

$product = Product::getProduct($product->getId());
$currGood = true;
if($product->acceptsCurrency(ProductCurrency::BITCOIN) && $seller->getBitcoin() == ''){
    $currGood = false;
}
if($product->acceptsCurrency(ProductCurrency::PAYPAL) && $seller->getPaypal() == ''){
    $currGood = false;
}
if($product->acceptsCurrency(ProductCurrency::PAYPALSUB) && $seller->getPaypal() == ''){
    $currGood = false;
}
if($product->acceptsCurrency(ProductCurrency::LITECOIN) && $seller->getLitecoin() == ''){
    $currGood = false;
}
if($product->acceptsCurrency(ProductCurrency::OMNICOIN) && $seller->getOmnicoin() == ''){
    $currGood = false;
}

if (count($url) == 3 && $url[2] == 'buy') {
    if (isset($_POST['action'])) {
        $response = array();
        $errorMessage = '';

        $action = $_POST['action'];

        switch ($action) {
            case 'purchase':
                if (isset($_POST['price']) && isset($_POST['currency']) && isset($_POST['email']) && isset($_POST['quantity'])) {
                    if ($_POST['price'] != $product->getPrice()) {
                        $errorMessage = 'RELOAD1';
                        break;
                    }

                    if (!$product->acceptsCurrency((int)$_POST['currency'])) {
                        $errorMessage = 'RELOAD2';
                        break;
                    }

                    if ($_POST['quantity'] < 1 || !ctype_digit($_POST['quantity'])) {
                        $errorMessage = 'RELOAD3';
                        break;
                    }

                    if ($product->getType() == ProductType::SERIAL && count($product->getSerials()) < $_POST['quantity']) {
                        $errorMessage = 'RELOAD4';
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
                        $errorMessage = 'RELOAD5';
                        break;
                    }

                    $order = new Order();

                    $order->setProductId($product->getId());
                    $order->setQuantity((int)$_POST['quantity']);
                    $order->setCurrency((int)$_POST['currency']);
                    $order->setFiat($product->getPrice());
                    $order->setEmail($_POST['email']);
                    $order->setIp(getRealIp());
                    $order->setQuestions($questions);


                    if ($_POST['couponCode'] != '') {
                        $coupon = new Coupon();

                        if (!$coupon->readByNameAndSellerId($_POST['couponCode'], $seller->getId()) || count(Order::getOrdersByCoupon($coupon->getId())) >= $coupon->getMaxUsedAmount()) {
                            $errorMessage = 'RELOAD6';
                            break;
                        } else {
                            if (!in_array($product->getId(), $coupon->getProducts())) {
                                $errorMessage = 'RELOAD61';
                                break;
                            }

                            $order->setCouponId($coupon->getId());
                            $order->setCouponUsed(true);
                            $order->setCouponName($coupon->getName());
                            $order->setCouponReduction($coupon->getReduction());
                        }
                    }

                    if ($_POST['affiliate'] != '') {
                        $affiliate = new Affiliate();

                        if (!$affiliate->read($_POST['affiliate']) || $affiliate->getProductId() != $seller->getId()) {
                            $errorMessage = 'RELOAD7';
                            break;
                        } else {
                            $order->setAffiliateUsed(true);
                            $order->setAffiliate($affiliate->getId());
                        }
                    }

                    $order->setSuccessUrl($product->getSuccessUrl());

                    $order->create();

                    $mailer = new Mailer();
                    $mailer->sendTemplate(EmailTemplate::INVOICE, $order->getEmail(), '', $product->getTitle(), $config['url']['protocol'] . $config['url']['domain'] . $product->getUrl() . '/i/' . $order->getId(), $config['url']['protocol'] . $config['url']['domain'] . $product->getUrl(),  $config['url']['protocol'] . $config['url']['domain'] . '/u/' . $seller->getUniqueId(), $seller->getUsername());

                    if ($order->getFiat() != 0) {
                        if ($order->getCurrency() == ProductCurrency::PAYPAL || $order->getCurrency() == ProductCurrency::PAYPALSUB) {
                            $response['action'] = 'pp-checkout';
                            $response['data'] = array('sub' => $order->getCurrency() == ProductCurrency::PAYPALSUB, 'business' => $seller->getPaypal(), 'itemname' => $product->getTitle(), 'itemnumber' => $product->getId(), 'amount' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'custom' => $order->getTxid(), 'shipping' => $product->getRequireShipping(), 'quantity' => 1, 'sub-length' => $product->getPaypalSubLength(), 'sub-unit' => $product->getPaypalSubUnit(), 'success_url' => $product->getSuccessUrl());

                            $order->setMerchant($seller->getPaypal());
                            $order->update();
                        } else if ($order->getCurrency() == ProductCurrency::BITCOIN || $order->getCurrency() == ProductCurrency::LITECOIN || $order->getCurrency() == ProductCurrency::OMNICOIN) {
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

                            $tx = $cp->CreateTransaction(array('buyer_name' => '', 'buyer_email' => $order->getEmail(), 'amount' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'currency1' => 'USD', 'currency2' => $currency, 'address' => $address, 'item_name' => $product->getTitle(), 'item_number' => $product->getId(), 'custom' => $order->getTxid(), 'ipn_url' => $config['url']['protocol'] . $config['url']['domain'] . '/ipn/coinpayments/', 'quantity' => 1, 'success_url' => $product->getSuccessUrl()));

                            if ($tx['error'] != 'ok') {
                                $errorMessage = json_encode($tx);
                                break;
                            }


                            $order->setNative($tx['result']['amount']);
                            $order->setProcessorTxid($tx['result']['txn_id']);
                            $order->setQrUrl($tx['result']['qrcode_url']);
                            $order->update();

                            $response['action'] = 'display-crypto';
                            $response['data'] = array('txid' => $order->getTxid(), 'id' => $order->getId(), 'qr' => $tx['result']['qrcode_url']);
                        }
                    } else {
                        $order->setNative($order->getFiat());
                        $order->setProcessorTxid('free-purchase');

                        $order->process();

                        $order->update();

                        $response['action'] = 'display-free';
                    }

                } else {
                    $errorMessage = 'RELOAD9';
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

                            $order->setCryptoTo($tx['result']['payment_address']);
                            $order->update();

                            if ($tx['error'] == 'ok') {
                                $response = array('title' => $product->getTitle(), 'txid' => $order->getTxid(), 'price' => $order->calculateFiatWithCoupon() * $order->getQuantity(), 'created' => gmdate('Y-m-d', $tx['result']['time_created']), 'expires' => $tx['result']['time_expires'], 'status' => $tx['result']['status'], 'coin' => $tx['result']['coin'], 'amount' => $tx['result']['amountf'], 'received' => $tx['result']['receivedf'], 'confirms' => $tx['result']['recv_confirms'], 'address' => $tx['result']['payment_address'], 'success_url' => $product->getSuccessUrl());
                            }
                        } else {
                            $errorMessage = 'RELOAD10';
                            break;
                        }
                    } else {
                        $errorMessage = 'RELOAD11';
                        break;
                    }
                } else {
                    $errorMessage = 'RELOAD12';
                    break;
                }
                break;
        }

        if ($errorMessage != '') {
            die(json_encode(array('error' => true, 'errorMessage' => $errorMessage)));
        } else {
            die(json_encode(array('error' => false, 'response' => $response)));
        }
    }
}

if(isset($_GET['redeemcoupon']) && $_GET['redeemcoupon'] == "true" && isset($_GET['couponcode']) && ctype_alnum($_GET['couponcode'])) {
    $coupon = new Coupon();

    if ($coupon->readByNameAndSellerId($_GET['couponcode'], $seller->getId()) && in_array($product->getId(), $coupon->getProducts())) {
        if (count(Order::getOrdersByCoupon($coupon->getId())) >= $coupon->getMaxUsedAmount()) {
            die('used');
        } else {
            die($coupon->getReduction() / 100);
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
        $message->setMessage(stripTags($_POST['message']));

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
            $affiliate = false;
        }
    }
}
$available = "-1";
if($product->getType() == ProductType::SERIAL){
    $available = count($product->getSerials());
}
___header($product->getTitle(), false, true, strip_tags($product->getDescription()));
$imgsrc = $product->getProductImgSrc($config['upload']['directory']);

if ($uas->hasMessage(true)) {
    $uas->printMessages();
}
?>
<input type="hidden" id="quantity" name="quantity" value="1" />
<input type="hidden" id="couponCodeResult" name="couponCode" />
<input type="hidden" id="pricePrijs" value="<?php echo $product->getPrice(); ?>" />
<input type="hidden" id="currency" name="currency" value="<?php echo ProductCurrency::PAYPAL; ?>" />

    <section id="product" class="product-card">
        <div class="product-image <?php if($product->getProductImg() == '0') { echo 'empty-image'; } ?>">
            <span class="helper">
                <img style="width: 100%;" src="<?php echo $imgsrc; ?>">
        </div>

        <div class="step-1" id="step1">
            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                        <span class="quantity-left muted"><?php  if( $product->getType() == ProductType::SERIAL) { echo $product->makeSerialString(); } ?></span>
                    </div>
                </div>
            </div>
            <!-- product-card-body -->

            <div class="product-card-info">
                    <div class="tab-pane active" id="info">
                        <div class="product-description"><?php echo $product->getDescription(); ?></div>
                    </div>
            </div>
            <!-- product-card-info -->

            <div class="product-card-quantity">
                <button type="button" id="card_btn_minus" class="btn btn-default btn-number" data-type="minus">
                    <i class="icon-minus fa fa-minus"></i>
                </button>
                <span class="muted quantity"><i>1</i>
                </span>
                <button type="button" id="card_btn_plus" class="btn btn-default btn-number" data-type="plus">
                    <i class="icon-plus fa fa-plus"></i>
                </button>
                <span class="pull-right price-total">$<i><?php echo $product->getPrice(); ?></i>
                </span>
            </div>
            <?php
            if(count($product->getCurrency()) > 0 && $currGood){
                ?>
            <div class="product-card-button">
                <a class="btn btn-success purchase-btn" href="#step2">Purchase</a>
            </div>
            <?php } else {
                ?>
                <div class="product-card-button">
                <a class="btn btn-success" href="#step2">Your payment methods are not setup correctly.</a>
            </div>
            <?php
            } ?>
            <!-- product-card-quantity + button -->

        </div>
        <!-- step 1 -->

        <div class="step-2" id="step2" style="display:none;">
            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                    </div>
                </div>
            </div>
            <!-- product-card-body -->
            <div class="product-card-email">
                <input type="email" class="form-control" style="border: solid 1px #66CCFF;" name="email" id="email" placeholder="E-mail">
            </div>
            <div class="product-card-payment-method">
                <div class="methods">
                    <?php if($product->acceptsCurrency(ProductCurrency::PAYPAL)) {?>
                        <input type="radio" id="pp" name="currency_radio" value="pp" onclick="pay(<?php echo ProductCurrency::PAYPAL; ?>);" />
                        <label class="radiolabel" for="pp">
                            <div class="method">
                            <span class="align-table">
                                <span class="method-abbr" data-color="blue">PP</span>
                                <span class="method-name">PayPal</span>
                            </span>
                                <span class="delivery" data-delivery="Instant Delivery"></span>
                                <span class="pull-right price" data-prepend="" data-price="" data-append=""></span>
                            </div>
                        </label>
                    <?php } if($product->acceptsCurrency(ProductCurrency::PAYPALSUB)) {?>
                        <input type="radio" id="ppsub" name="currency_radio" value="ppsub" onclick="pay(<?php echo ProductCurrency::PAYPALSUB; ?>);" />
                        <label class="radiolabel" for="ppsub">
                            <div class="method">
                            <span class="align-table">
                                <span class="method-abbr" data-color="blue">SUB</span>
                                <span class="method-name">Subscription</span>
                            </span>
                                <span class="delivery" data-delivery="Instant Delivery"></span>
                                <span class="pull-right price" data-prepend="" data-price="" data-append=""></span>
                            </div>
                        </label>
                    <?php } if($product->acceptsCurrency(ProductCurrency::BITCOIN)) {
                    ?>
                        <input type="radio" id="btc" name="currency_radio" value="btc" onclick="pay(<?php echo ProductCurrency::BITCOIN; ?>);" />
                        <label class="radiolabel" for="btc">
                            <div class="method">
                            <span class="align-table">
                                <span class="method-abbr" data-color="yellow">BTC</span>
                                <span class="method-name">Bitcoin</span>
                            </span>
                                <span class="delivery" data-delivery="1 Confirmation"></span>
                                <span class="pull-right price" data-prepend="" data-price="" data-append=""></span>
                            </div>
                        </label>

                    <?php } if($product->acceptsCurrency(ProductCurrency::LITECOIN)) {
                        ?>
                        <input type="radio" id="ltc" name="currency_radio" value="ltc" onclick="pay(<?php echo ProductCurrency::LITECOIN; ?>);" />
                        <label class="radiolabel" for="ltc">
                            <div class="method">
                            <span class="align-table">
                                <span class="method-abbr">LTC</span>
                                <span class="method-name">Litecoin</span>
                            </span>
                                <span class="delivery" data-delivery="1 Confirmation"></span>
                                <span class="pull-right price" data-prepend="" data-price="" data-append=""></span>
                            </div>
                        </label>
                    <?php } if($product->acceptsCurrency(ProductCurrency::OMNICOIN)) {
                        ?>
                        <input type="radio" id="omc" name="currency_radio" value="omc" onclick="pay(<?php echo ProductCurrency::OMNICOIN; ?>);" />
                        <label class="radiolabel" for="omc">
                            <div class="method">
                            <span class="align-table">
                                <span class="method-abbr" data-color="purple">OMC</span>
                                <span class="method-name">Omnicoin</span>
                            </span>
                                <span class="delivery" data-delivery="1 Confirmation"></span>
                                <span class="pull-right price" data-prepend="" data-price="" data-append=""></span>
                            </div>
                        </label>
                    <?php } ?>
                </div>
            </div>

            <div class="product-card-email">
                <input type="text" id="couponCode" class="form-control coupon-input" placeholder="Coupon code" >
                <button class="btn btn-success coupon-button" onclick="redeemCoupon();">Apply</button>
                <label id="couponResult" style="display: none;"></label>
                <div class="clearfix"></div>
            </div>

            <div class="product-card-button nb">
                <a class="btn btn-success purchase-btn" href="#customquestions">Purchase</a>
            </div>
            <!-- product-card-quantity + button -->

        </div>
        <!-- step 2 -->

        <div class="customquestions" id="customquestions" style="display:none">

            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                    </div>
                </div>
            </div>
            <?php
            $x = 0;
            foreach($product->getQuestions() as $question){
                $x++;
                ?>
            <div class="product-card-email">
                <label for="custom-question"><?php echo htmlspecialchars($question); ?></label>
                <input type="text" name="custom-question-<?php echo $x; ?>" id='custom-question-<?php echo $x; ?>' class="form-control" placeholder="Question">
            </div>
            <?php
            } ?>

            <div class="product-card-button nb">
                <a class="btn btn-success purchase-btn" href="#preparing">Continue</a>
            </div>
        </div>

        <div class="preparing" id="preparing" style="display:none">
            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                    </div>
                </div>
            </div>
            <div class="status-block"><i class="fa fa-spinner fa-spin"></i> We are preparing your transaction, please hold on.</div>
        </div>

        <div class="step-3" id="step3" style="display:none">
            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                    </div>
                </div>
            </div>
            <!-- product-card-body -->
            <div class="status-block status-block-complete finalstatus" style="display: none;"><h4><i class="fa fa-check"></i> Complete! Check your email!</h4></div>
            <div class="status-block status-block-failed finalstatus" style="display: none;"><h4><i class="fa fa-times"></i> Your transaction has expired.</h4></div>
            <div class="status-block status-block-normal finalstatus"><i class="fa fa-spinner fa-spin"></i> Awaiting Payment</div>
            <div class="col-md-4">
                <img src="/img/qr.png" class="qr">
            </div>
            <div class="col-md-8 right-block">Send
                <span class="amount finalamount"></span> <span class="finalcoin">BTC</span> to
                <div class="clearfix"></div>
                <input class="address finaladdress" value="" size="38">
                <div class="clearfix"></div>
                <span class="time finaltime">Loading.</span>
            </div>
            <div class="clearfix"></div>
            <div class="status-block pff">Payments Received</div>
                <span class="headings">
                    <span class="tr thead first-tr">Currency</span>
                    <span class="tr thead">Amount</span>
                    <span class="tr thead">Date</span>
                    <span class="tr thead">Received</span>
                </span>
            <div class="methods payments-received">
                <form>
                    <div class="method new">
                            <span class="tr first-tr">
                                <span class="method-abbr finalcoin" id="finalcoinColor" data-color="yellow">BTC</span>
                            </span>
                            <span class="tr">
                                <span class="sent finalamount" data-delivery="3 Confirmations">0.1234 BTC</span>
                            </span>
                            <span class="tr">
                                <span class="confirms finaldate"></span>
                            </span>
                            <span class="tr">
                                <span class="confirms"><span class="finalrecv"></span></span>
                            </span>
                    </div>
                </form>
            </div>
        </div>

        <?php if(isset($order) && ($order->getCurrency() == ProductCurrency::PAYPAL ||$order->getCurrency() == ProductCurrency::PAYPALSUB)) {
            ?>

        <div class="paypal-invoice" id="paypal-invoice" style="display:none">
            <div class="product-card-body">
                <div class="row">
                    <div class="col-xs-7 product-details">
                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                        <span class="muted">by </span><a href="/u/<?php echo $seller->getUniqueId(); ?>" class="product-creator"><?php echo htmlspecialchars($seller->getUsername()); ?></a>
                    </div>
                    <div class="col-xs-5 product-pricing">
                        <span class="price">$<?php echo htmlspecialchars($product->getPrice()); ?></span>
                    </div>
                </div>
            </div>
            <?php if(!$order->isCompleted()) { ?>
                <div class="status-block status-block-normal finalstatus"><i class="fa fa-spinner fa-spin"></i> Awaiting Payment</div>
            <?php } else { ?>
            <div class="status-block status-block-complete finalstatus" style="display: none;"><h4><i class="fa fa-check"></i> Complete! Check your email!</h4></div>
            <?php } ?>

            <table class="table pp-table">
                <thead>
                <tr>
                    <td>Product</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <?php if($order->isCouponUsed()) { ?><td>Reduction</td> <?php }?>
                    <td>Total</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $product->getTitle(); ?></td>
                    <td>$<?php echo $product->getPrice(); ?></td>
                    <td><?php echo $order->getQuantity(); ?></td>
                    <?php if($order->isCouponUsed()) {?> <td>- <?php echo (($order->getFiat() * $order->getQuantity()) - ($order->calculateFiatWithCoupon() * $order->getQuantity())); ?></td><?php }?>
                    <td>$<?php if($order->isCouponUsed()) {echo number_format($order->calculateFiatWithCoupon() * $order->getQuantity(), 2); } else { echo number_format($order->getFiat() * $order->getQuantity(),2);}?> </td>
                </tr>
                </tbody>
            </table>
            <?php if(!$order->isCompleted()) { ?>
            <form action='https://www.paypal.com/cgi-bin/webscr' method='post' id='ppform'>
                <input type='hidden' name='cmd' id='paycmd' value='<?php if($order->getCurrency() == ProductCurrency::PAYPALSUB) {echo '_xclick-subscriptions'; } else {echo '_xclick';} ?>'>
                <input type='hidden' name='currency_code' value='USD'>
                <input type='hidden' name='business' value='<?php echo $order->getMerchant(); ?>'>
                <input type='hidden' name='item_name' value='<?php echo $product->getTitle(); ?>'>
                <input type='hidden' name='item_number' value='<?php echo $product->getId(); ?>'>
                <input type='hidden' name='amount' value='<?php echo $order->calculateFiatWithCoupon(); ?>'>
                <input type='hidden' name='custom' value='<?php echo $order->getTxid(); ?>'>
                <input type='hidden' name='notify_url' value='<?php echo $config['url']['protocol'] . $config['url']['domain'] . '/ipn/paypal'; ?>'>
                <input type='hidden' name='return' value='<?php echo $product->getSuccessUrl(); ?>'>
                <input type='hidden' name='cancel_return' value=''>
                <input type='hidden' name='invoice' value=''>
                <input type='hidden' name='allow_amount' value='0'>
                <input type='hidden' name='want_shipping' value='<?php echo $product->getRequireShipping() ? 1 : 0; ?>'>
                <input type='hidden' name='quantity' value='<?php echo $order->getQuantity(); ?>'>
                <input type='hidden' name='no_shipping' value='<?php echo $product->getRequireShipping() ? 0: 1; ?> '>
                <input name='a3' type='hidden' value='<?php echo $order->calculateFiatWithCoupon() * $order->getQuantity(); ?>'>
                <input name='t3' type='hidden' value='<?php echo $product->getPaypalSubLength(); ?>'>
                <input name='p3' type='hidden' value='<?php echo $product->getPaypalSubUnit(); ?>'>
                <input name='src' type='hidden' value='1'>
            <input type="submit" class="btn btn-success pull-right" style="margin-bottom: 15px; margin-right: 10px;" value="Pay Now" />
                </form>
            <div class="clearfix"></div>
            <?php } ?>
        </div>
        <?php } ?>
    </section>

<?php if($affiliate) {
    ?>
    <div class="modal fade" id="modal-affiliate" tabindex="-1" role="dialog" aria-labelledby="modal-compose">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalsmth">Become an affiliate</h4>
                </div>
                <form method="post">
                    <div class="modal-body compose-body" style="padding:15px;">
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
                            <input name="email" required="true" type="email" class="form-control" placeholder="Email Address">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button name='affiliate-register' type="submit" class="btn btn-primary">Become an affiliate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
}?>
<script>
    $(document).ready(function() {
        $('.product-description img').each(function() {
            $(this).wrap('<a href="' + $(this).attr('src') + '" data-lightbox="product"></a>');
        });

        $('#modal-affiliate').modal();
        var urlhash = document.location.hash.toString();
        var urlsplit = urlhash.split("-");

        var urlstep = urlsplit[0];
        var productid = urlsplit[1];
        if (productid) {
            console.log('++++++++++++++++++++++++++++++++++++');
            console.log('PID ' + productid);
            console.log('++++++++++++++++++++++++++++++++++++');
        }
        // try this by doing #step2-2333 after product.html, feel free to pull data with ajax :D

        /*if (urlstep == '#customquestions') {
            $('#step1').hide();
            $('#step2').hide();
            $('#customquestions').show();
            $('#step3').hide();
        }
        if (urlstep == '#step3') {
            $('#step1').hide();
            $('#step2').hide();
            $('#customquestions').hide();
            $('#step3').show();
        }
        if (urlstep == '#step2') {
            $('#step1').hide();
            $('#step2').show();
            $('#customquestions').hide();
            $('#step3').hide();

        }
        if (urlstep == '#step1') {
            $('#step1').show();
            $('#step2').hide();
            $('#customquestions').hide();
            $('#step3').hide();
        }*/

        var productPrice = <?php echo $product->getPrice(); ?>;
        var quantity = 1;
        var available = <?php echo $available; ?>; // optional, just added this incase this will get implemented later
        var minimum = 1; //this will always be 1, I presume
        // <input type="text" class="quantity-text form-control" aria-label="..." value="$10.00">
        // <button type="button" id="card_btn_minus" class="btn btn-default btn-number" data-type="minus">
        // <button type="button" id="card_btn_plus" class="btn btn-default btn-number" data-type="plus">

        $('.span.quantity-left.muted').html(available + ' left');
        $('#card_btn_minus').click(function() {
            console.log('-, ' + quantity);
            if (quantity > minimum) {
                quantity -= 1;
                var inputValue = productPrice * quantity;
                $('.product-card-body .price').html('$' + inputValue.toFixed(2));
                $('.price-total i').html(inputValue.toFixed(2));
                $('.price-total').html('$'+inputValue.toFixed(2));
                $('.product-card-quantity .quantity i').html(quantity);
                var left = available - quantity;
                if(left < 1000) {
                    $('span.quantity-left.muted').html(left + ' left');
                } else {
                    $('span.quantity-left.muted').html('');
                }
                $('#quantity').val(quantity);
                $('#pricePrijs').val(inputValue);
            }
        });
        $('#card_btn_plus').click(function() {
            if (quantity >= minimum && quantity < available) {
                quantity += 1;
                var inputValue = productPrice * quantity;
                $('.product-card-body .price').html('$' + inputValue.toFixed(2));
                $('.price-total i').html(inputValue.toFixed(2));
                $('.price-total').html('$'+inputValue.toFixed(2));
                $('.product-card-quantity .quantity i').html(quantity);
                var left = available - quantity;
                if(left < 1000) {
                    $('span.quantity-left.muted').html(left + ' left');
                }else {
                    $('span.quantity-left.muted').html('');
                }
                $('#quantity').val(quantity);
                $('#pricePrijs').val(inputValue);
            }
        });
        $('#step1 .purchase-btn').click(function() {
            $('#step1').hide();
            $('#step2').show();
            $('#customquestions').hide();
            $('#step3').hide();
        });
        $('#step2 .purchase-btn').click(function() {
        if(validateEmail($('#email').val())){
            $('#step1').hide();
            $('#step2').hide();
            $('#step3').hide();
            $('#customquestions').show();
        } else {
            alert('Please fill in a valid email!');
        }
        });
        $('#customquestions .purchase-btn').click(function() {
            $('#step1').hide();
            $('#step2').hide();
            $('#customquestions').hide();
            if(currency == <?php echo ProductCurrency::PAYPAL; ?> || currency == <?php echo ProductCurrency::PAYPALSUB; ?>){
                $('#preparing').show();
                checkout();
            }else {
                $('#preparing').show();
                checkout();
            }
        });

        $('input[name=currency_radio]:radio').change(function(){
        });
    });

    $('#affiliate').modal('show');

    var apiEndpoint = '<?php echo $product->getUrl(); ?>/buy';
    var currency = '0';

    $('#quantity').change(function() {
        if ($(this).val() < 1) {
            $(this).val('1');
        }

        $('.total').html('$' + ((($(this).val() * <?php echo $product->getPrice(); ?>) * 100) / 100).toFixed(2));
    });

    function validateEmail(email) {
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(email);
    }

    function redeemCoupon()
    {
        if($('#couponCodeResult').val() == '') {
            $.get("?redeemcoupon=true&couponcode=" + $('#couponCode').val(), function (data) {
                $('#couponResult').show();
                switch (data) {
                    case "false":
                        $('#couponResult').text('The coupon you entered is not valid.');
                        $('#couponResult').css('color', '#ED000C');
                        break;
                    case "used":
                        $('#couponResult').text('The maximum usage limit of this coupon has been reached.');
                        $('#couponResult').css('color', '#ED000C');
                        break;
                    default:
                        var spared = ($('#pricePrijs').val() * data);
                        $('#couponResult').text(" " + $('#couponCode').val() + ' applied, you saved $' + spared.toFixed(2));
                        $('#couponResult').css('color', '#72b611');
                        $('#couponCode').prop("readonly", true);
                        $('#couponCodeResult').val($('#couponCode').val());
                        var inputValue = $('#pricePrijs').val() - spared;
                        $('#pricePrijs').val(inputValue);
                        $('.product-card-body .price').html('$' + inputValue.toFixed(2));
                        $('.price-total i').html(inputValue.toFixed(2));
                        $('.price-total').html('$'+inputValue.toFixed(2));
                        break;
                }
            });
        }
    }

    function pay(type) {
        $('#currency').val(type);
    }

    function checkout() {
        <?php
        $affId = false;

        $affUrl = count($url) == 3 && $url[2] == 'a';

        $affCookie = isset($_COOKIE[$product->getId() . '-affid']);

        if ($affUrl || $affCookie) {
            $affiliate = new Affiliate();

            if ($affiliate->read($affUrl ? $url['2'] : $_COOKIE[$product->getId() . '-affid'])) {
                $affId = $affiliate->getId();
            }
        }
        ?>
        var data = {'action': 'purchase', 'price': <?php echo $product->getPrice(); ?>, 'currency': $('#currency').val(), 'email': $('#email').val(), 'quantity': $('#quantity').val(), 'couponCode': $('#couponCodeResult').val(), 'success_url': '<?php echo $product->getSuccessUrl(); ?>', 'affiliate': '<?php echo $affId; ?>'};
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
                        var color = '';
                        switch($('#currency').val()){
                            case '<?php echo ProductCurrency::BITCOIN; ?>':
                                color = 'yellow';
                                break;
                            case '<?php echo ProductCurrency::OMNICOIN; ?>':
                                color = 'purple';
                                break;
                        }
                        $('#finalcoinColor').attr('data-color', color);
                        $('.qr').prop('src', data.response.data.qr);
                        $('.invoice-link').html($('.invoice-link').html() + data.response.data.id);
                        $('#preparing').slideUp(1000, function() {
                            setTimeout(function() { $('.step-3').slideDown(1000); }, 1000);
                        });


                        var intervalx = setInterval(update(data), 15000);
                        update(data);
                        countdown();
                        break;
                    case 'display-free':
                        $('.step-2').slideUp(1000, function() {
                            setTimeout(function() { $('.step-3').slideDown(1000); }, 1000);
                        });

                        $('.step-3 .panel-body').html("Your product has been sent to the email you provided.");

                        break;
                }
            }
        });
    }

    function update(data) {

        var data2 = {'action': 'checktx', 'txid': data.response.data.txid};
        $.post(apiEndpoint, data2, function(data) {
            data = $.parseJSON(data);
            if (data.response.received >= data.response.amount) {
                $('.status-block-normal').hide("slow");
                $('.status-block-failed').hide("slow");
                $('.status-block-complete').show("slow");
                $('.finalrecv').html(data.response.received);
                clearInterval(intervalCd);
                clearInterval(intervalx);
            } else if (((data.response.expires - ((new Date).getTime() / 1000)) / 60) <= 0) {
                $('.status-block').hide("slow");
                $('.status-block-failed').show("slow");
                clearInterval(intervalx);
                clearInterval(intervalCd);
            } else {
                toGo = data.response.expires;
                $('.finalcoin').text(data.response.coin);
                $('.finalamount').html(data.response.amount);
                $('.finaladdress').val(data.response.address);
                $('.finaldate').html(data.response.created);
                $('.finalrecv').html(data.response.received);
            }
        });
    }

    function countdown() {
        if(toGo != null){
        var nextmonth = timeConverter(toGo);
        var now = new Date();
        var timeDiff = nextmonth.getTime() - now.getTime();

        var seconds = Math.floor(timeDiff / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);

        hours%=24;
        minutes%=60;
        seconds%=60;


        $('.finaltime').html(fixNumber(hours) + ':' + fixNumber(minutes) + ':' + fixNumber(seconds));
        }
        if(intervalCd == null){
            intervalCd = setInterval(countdown, 1000);
        }
    }
    var toGo = null;
    var intervalCd = null;

    function timeConverter(UNIX_timestamp){
        var a = new Date(UNIX_timestamp*1000);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        var time = date + ',' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
        return new Date(time);
    }

    function fixNumber(n){
        return n > 9 ? "" + n: "0" + n;
    }

    <?php if(isset($order)) {
?>
    $('#step1').hide();
    $('#step2').hide();
    $('#customquestions').hide();
    <?php
    if($order->getCurrency() == ProductCurrency::OMNICOIN || $order->getCurrency() == ProductCurrency::BITCOIN ||$order->getCurrency() == ProductCurrency::LITECOIN)
    {
    $currency = '';
    $color = '';
    switch($order->getCurrency()) {
    case ProductCurrency::BITCOIN:
    $currency = 'BTC';
    $color = 'yellow';
    break;
    case ProductCurrency::LITECOIN:
    $currency = 'LTC';
    break;
    case ProductCurrency::OMNICOIN:
    $currency = 'OMC';
    $color = 'purple';
    break;
    }
    ?>

    $('#step3').show();
    $('.qr').prop('src', '<?php echo $order->getQrUrl(); ?>');
    $('.invoice-link').html($('.invoice-link').html() + '<?php echo $order->getId(); ?>');
    $('.finaldate').html('<?php echo date_format(new DateTime($order->getDate()), 'Y/m/d'); ?>');
    $('.price-total').text('$<?php echo number_format($order->calculateFiatWithCoupon() * $order->getQuantity(), 2); ?>');
    $('.finalamount').html('<?php echo $order->getNative(); ?>');
    $('.finaladdress').val('<?php echo $order->getCryptoTo(); ?>');
    $('#finalcoinColor').attr('data-color', '<?php echo $color; ?>');
    $('.finalcoin').text('<?php echo $currency; ?>');

    var data = {'response': {'data': {'txid': '<?php echo $order->getTxid(); ?>'}}};
    var intervalx = setInterval(update(data), 15000);
    update(data);
    countdown();
    <?php
    } else {
        ?>
        $('#paypal-invoice').show();
    <?php
    }
}
?>
</script>
<script type="text/javascript" src="/js/lightbox.min.js"></script>
</body>
</html>