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
        'pass' => 'root'
    ),
    'recaptcha' => array(
        'site' => '6Lcg3ggTAAAAAOnfpMZRXFsGyBdus4YgI2HVL6hi',
        'secret' => '6Lcg3ggTAAAAAESX9PoDbx2hrOVuuOJOqQuBTrku'
    ),
    'sendgrid' => array(
        'username' => 'linh721990',
        'password' => 'StAdus88ECru2adESAFRezeruCrAWraX'
    ),
    'upload' => array(
        'directory' => '/home/apache/uploads/',
        'allowedFiles' => array(
            'pdf',
            'txt',
            'mpeg',
            'mp4',
            'zip',
            'rar'
        ),
        'cloudstubs' => array(
            'dll',
            'exe'
        ),
        'profilepics' => array('png', 'jpg', 'jpeg')
    ),
    'coinpayments' => array(
        'private' => 'A2E6B7760D340FDf066B9020008944fA8061a907c28ff84f31f81dbf23b6deCa',
        'public' => '80cb0ef63e6919a898d227a4b7146180613c34e9931f92a0ada1cbfc1f908c64',
        'merchant-id' => '6e66d609c8c47e8a7abd783a10e6eb94',
        'ipn-secret' => '6372846284728472'
    )
);