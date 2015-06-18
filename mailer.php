<?php
//TODO: Move to dedicated admin panel
//A very, very rough script for sending emails
die();
require_once('lib/functions.php');

$mailer = new Mailer();

$mailer->send('scrible97@gmail.com', 'Thanks for Purchasing LAN Accounts Lv30 Unverified<br>Here is what you bought: richistin:richistin10<br>', 'Your Purchase on PayIvy.com');