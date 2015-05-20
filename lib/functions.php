<?php
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
}

abstract class UserAccountType {
	const PREMIUM = 0;
	const ADMIN = 1;
}

abstract class ProductType {
	const DOWNLOAD = 0;
	const SERIAL = 1;
	const NETSEAL = 2;
}

abstract class ProductCurrency {
	const PAYPAL = 0;
    const PAYPALSUB = 1;
	const BITCOIN = 2;
	const LITECOIN = 3;
	const OMNICOIN = 4;
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
    $try = array(__DIR__ . '/classes/' . $name . '.class.php', __DIR__ . '/' . $name . '.php');

    foreach ($try as $file) {
        if (file_exists($file)) {
            require_once($file);
        }
    }
}

function validateReCaptcha($verify) {
	return true; //Remove before flight
	
	global $config;
	
	if (preg_match('/[^A-Za-z0-9_-]/', $verify)) {
		echo 'mhm';
		return false;
	}
	
	var_dump(('https://www.google.com/recaptcha/api/siteverify?secret=' . $config['recaptcha']['secret'] . '&response=' . $verify));
	
	curl_setopt($ch = curl_init(), CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $config['recaptcha']['secret'] . '&response=' . $verify);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	$output = curl_exec($ch);
	curl_close($ch);
	var_dump($output);
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
	$value = number_format($value);
	
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
    return addslashes(preg_replace("/<(br|h1|h2|h3|h4|h5|h6|h7|h8|h9|h10|i|u|ul|li|ol|blockquote|a|img|b) [^>]*>/", "<$1>", strip_tags($string, '<br><h1><h2><h3><h4><h5><h6><h7><h8><h9><h10><i><u><ul><li><ol><blockquote><a><img><b>')));
}