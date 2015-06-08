<?php
$config = array(
	'url' => array(
		'protocol' => '',
		'domain' => ''
	),
	'db' => array(
		'host' => 'localhost',
		'port' => '3306',
		'database' => 'payivy',
		'user' => 'root',
		'pass' => ''
	),
	'recaptcha' => array(
		'site' => '',
		'secret' => ''
	),
	'mandrill' => array(
		'key' => ''
	),
    'upload' => array(
        'directory' => '',
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
        'private' => '',
        'public' => '',
        'merchant-id' => '',
        'ipn-secret' => ''
    ),
    'encryption' => array(
        'aes' => ''
    )
);