<?php
$config = array(
	'url' => array(
		'protocol' => "http://",
		'domain' => 'localhost'
	),
	'db' => array(
		'host' => 'localhost',
		'port' => '3306',
		'database' => 'payivy',
		'user' => 'root',
		'pass' => ''
	),
	'recaptcha' => array(
		'site' => '6LdFGAETAAAAANXW3WOJ1GWG5Qj7QLs3Rcvl_Zcb',
		'secret' => '6LdFGAETAAAAAN6qT3m3Ih5yeTYF7zcAmO6fyT9u'
	),
	'mandrill' => array(
		'key' => 'j5NqyHJC1P96lNPgS2SF8w'
	),
    'upload' => array(
        'directory' => 'C:\wamp\www\uploads\\',
        'allowedFiles' => array(
            'pdf',
            'txt',
            'mpeg',
            'mp4',
            'zip',
            'rar'
        )
    ),
    'coinpayments' => array(
        'private' => '04291a8BB788D5B30fBE32F46f035957f5f6760747eBD85B1c0ba00C7863760f',
        'public' => '30c28debb2508e68d7fb5325680e8d4b7b74bb562ac46618eb9ebaf300e2574a',
        'mercant-id' => '6e66d609c8c47e8a7abd783a10e6eb94',
        'ipn-secret' => '6372846284728472'
    )
);