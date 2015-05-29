<?php
if(!isset($_POST['currency']) || !isset($_POST['product_title']) || !isset($_POST['product_price_usd']) || !isset($_POST['to_address'])) {

}

$after_success_url = isset($_POST['after_success_url']) ? $_POST['after_success_url'] : "";

$currency = $_POST['currency'];
$type = "";
$address_to = "";

if($currency >= 50) {
    $type = "crypto";

    if(!ctype_alnum($_POST['to_address'])) {
        die();
    }

    $address_to = $_POST['to_address'];
}

if($currency == ProductCurrency::PAYPAL) {
    $type = "pp";

    if(!filter_var($_POST['to_address'], FILTER_VALIDATE_EMAIL)) {
        die();
    }

    $address_to = $_POST['to_address'];
}

if($currency == ProductCurrency::PAYZA) {
    $type = "payza";

    if(!filter_var($_POST['to_address'], FILTER_VALIDATE_EMAIL)) {
        die();
    }

    $address_to = $_POST['to_address'];
}












