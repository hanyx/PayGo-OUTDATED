<?php
$config = array(
	'url' => array(
		'protocol' => '',
		'domain' => ''
	),
	'db' => array(
		'host' => '',
		'port' => '',
		'database' => '',
		'user' => '',
		'pass' => ''
	),
	'recaptcha' => array(
		'site' => '',
		'secret' => ''
	),
	'mandrill' => array(
		'key' => ''
	),
    'sendgrid' => array(
        'username' => '',
        'password' => ''
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
    )
);