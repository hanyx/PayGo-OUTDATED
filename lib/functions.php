<?php

require_once('lib/password.php');
require_once('../private/config.php');
require_once('views/seller/header.php');
require_once('views/seller/footer.php');


abstract class PaypalSubscriptionUnit {
    const DAY = 0;
    const MONTH = 1;
    const YEAR = 2;
}

abstract class TwoFactorRequestAction {
	const ACTIVATE = 0;
	const RESET = 1;
	const UPDATEPASSWORD = 2;
	const UPDATEPAYMENTDETAILS = 3;
}

abstract class EmailTemplate {
	const ACTIVATE = 0;
	const RESET = 1;
	const UPDATEPASSWORD = 2;
	const UPDATEPAYMENTDETAILS = 3;
	const SELLERMESSAGE = 4;
	const USERMESSAGE = 5;
	const AFFILIATEPAID = 6;
    const DOWNLOAD = 7;
    const NETSEALS = 8;
    const SERIALS = 9;
    const OUTOFSTOCK = 10;
    const SELLERSALE = 11;
    const AFFILIATEREGISTER = 12;
}

abstract class UserAccountType {
	const PREMIUM = 0;
	const ADMIN = 100;
}

abstract class ProductType {
	const DOWNLOAD = 0;
	const SERIAL = 1;
	const NETSEAL = 2;
}

abstract class ProductCurrency {
	const PAYPAL = 0;
    const PAYPALSUB = 1;

	const BITCOIN = 50;
	const LITECOIN = 51;
	const OMNICOIN = 52;
}

abstract class MessageFolder {
	const INBOX = 0;
	const SENT = 1;
	const PRODUCTDELIVERY = 2;
}

abstract class FraudType {
	const UNAUTHCLAIM = 0;
	const CHARGEBACK = 1;
}

function __autoload($name) {
    $try = array(__DIR__ . '/classes/' . $name . '.class.php');

    foreach ($try as $file) {
        if (file_exists($file)) {
            require_once($file);
            return;
        }
    }
}

function validateReCaptcha($verify) {
    return true;
    global $config;

    if (preg_match('/[^A-Za-z0-9_-]/', $verify)) {
        return false;
    }

    curl_setopt($ch = curl_init(), CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $config['recaptcha']['secret'] . '&response=' . $verify);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $output = curl_exec($ch);
    curl_close($ch);

    if ($output == null){
        return false;
    } else if (!json_decode($output, true)['success']) {
        return false;
    }

    return true;
}

function getRealIp() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return trim($_SERVER['HTTP_CLIENT_IP']);
	} 
	
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return trim($_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	
	return trim($_SERVER['REMOTE_ADDR']);
}

function generateRandomString($length = 128) {
	return substr(hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true)), 0, $length);
}

function numberFormatLabel($value, $label) {
	$value = number_format($value, 2);
	
	$label = formatS($value, $label);
	
	return $value . ' ' . $label;
}

function formatS($value, $label) {
	return $value == 1 ? $label : ($label . 's');
}

function formatTime($seconds) {
	$seconds2 = time();
	$time = ($seconds2 - $seconds);
	$postfix = 'second';
	if ($time >= 60) {
		$time = $time / 60;
		$postfix = 'minute';
		if ($time >= 60) {
			$time = $time / 60;
			$postfix = 'hour';
			if ($time >= 24) {
				$time = $time / 24;
				$postfix = 'day';
				if ($time >= 7) {
					$time = $time / 7;
					$postfix = 'week';
					if ($time >= 4.34812) {
						$time = $time / 4.34812;
						$postfix = 'month';
						if ($time >= 52.1775) {
							$time = $time / 52.1775;
							$postfix = 'year';
						}
					}
				}
			}
		}
	}
	$time = floor($time);
	return $time . ' ' . formatS($time, $postfix);
}

function formatDate($time) {
	return date('l, M d Y, h:m a', $time);
}

function stripTags($string) {
    return preg_replace("/<([a-z][a-z0-9]*)(?:[^>]*(\ssrc=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/i", '<$1$2$3>', strip_tags($string, '<br><h1><h2><h3><h4><h5><h6><h7><h8><h9><h10><i><u><ul><li><ol><blockquote><a><img><b>'));
}