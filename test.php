<?php
require_once('lib/functions.php');

die();
$order = new Order();

$order->readByTxid('18c6850765f0705eb779b1b7976604430e816db17df73c5f93c048b739e53c2c', false);

$order->setCompleted(true);

$order->update();

die();
$mailer = new Mailer();

$mailer->send('Aaron.craig.pollard@gmail.com', "Hey there,<br><br>                    Thanks for your recent purchase on PayIvy.com.<br><br>                    Here's a custom message from the seller of the item: Please, leave a positive feedback here: http://consolecrunch.org/index.php?threads/selling-private-console-ids-100-privates-25-usd.16417/<br><br>Here are the serials you purchased:<br><br>                <b>0000000100840009140626E91440078B</b><br><br>              ", 'Your purchase on PayIvy');