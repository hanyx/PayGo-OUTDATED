<?php
date_default_timezone_set('America/Chicago');

require_once('lib/config.php');
require_once('lib/functions.php');

$uas = new UserAuthenticationSystem();
$pageManager = new PageManager($uas);

$uncategorized = 	new PageCategory(			'',				'', 			false);
$authPages = 		new SellerAuthPageCategory(	'', 			'', 			false);
$dashboard = 		new SellerPageCategory(		'Dashboard', 	'fa-eye',	true, 0);
$messages = 		new PageCategory(			'Messages', 	'fa fa-envelope-o', 	true, 0);
$affiliates = 		new PageCategory(			'Affiliates', 	'fa-user', 		true, 0);
$products =			new PageCategory(			'Products', 	'fa-list-alt', 	true, 0);
$spbox = 			new PageCategory(			'SPBox', 		'fa-star', 		true, 0);
$admin = 			new PageCategory(			'Admin', 		'fa-wrench', 	true, 1);

$pageManager->set404Page(	new Page(						array(	array('')), 													'views/404.php' 													));
$pageManager->setPermsPage(	new Page(						array(	array('')), 													'views/nopermission.php' 						                    ));

$uncategorized->addPage(	new Page(						array(	array('')), 													'views/home.php' 													));
$uncategorized->addPage(	new Page(						array(	array('v', '*'),
    																array('v', '*', 'a', '*'),
    														        array('v', '*', 'buy')), 							        	'views/product.php', 							'', true			));
$uncategorized->addPage(	new Page(						array(	array('ipn', 'paypal'),
                                                                    array('ipn', 'coinpayments')), 									'views/home.php' 													));
$uncategorized->addPage(	new Page(						array(	array('download', '*')), 										'views/download.php' 												));

$uncategorized->addPage(new Page(array(array('l', '*')), 'views/licensing_product.php'));
$uncategorized->addPage(new Page(array(array('gateway')), 'views/gateway.php'));
$uncategorized->addPage(new Page(array(array('gatewayview')), 'views/gatewayview.php'));

$authPages->addPage(		new Page(						array(	array('seller', 'login'),
																	array('seller', 'login', 'activate', '*'),
																	array('seller', 'login', 'update', '*'),
																	array('seller', 'logout')),										'views/seller/login.php' 											));
$authPages->addPage(		new Page(						array(	array('seller', 'register')),									'views/seller/register.php' 					 					));
$authPages->addPage(		new Page(						array(	array('seller', 'reset'), 
																	array('seller', 'reset', '*')),									'views/seller/reset.php' 						 					));
$dashboard->addPage(		new Page(						array(	array('seller'),
                                                                    array('seller', 'chart')), 										'views/seller/home.php', 						'Dashboard'			));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings')),				                	'views/seller/settings-user.php', 				'User Settings'			));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings', 'payments')),		               	'views/seller/settings-payment.php', 			'Payment Settings'	));
$messages->addPage(			new SellerMessagesInboxPage(	array(	array('seller', 'messages', 'inbox'),
																	array('seller', 'messages', 'inbox', 'view', '*'),
																	array('seller', 'messages', 'inbox', 'delete', '*'),
																	array('seller', 'messages')),									'views/seller/messages-inbox.php', 				'Inbox'				));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'sent'),
																	array('seller', 'messages', 'sent', 'view', '*')),				'views/seller/messages-sent.php', 				'Sent'				));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'product-delivery'),
																	array('seller', 'messages', 'product-delivery', 'view', '*')), 	'views/seller/messages-product-delivery.php', 	'Product Delivery'	));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'compose'),
																	array('seller', 'messages', 'reply', '*')), 					'views/seller/messages-compose.php', 			'Compose'			));
$affiliates->addPage(		new Page(						array(	array('seller', 'affiliates'),
																	array('seller', 'affiliates', 'pay', '*')), 					'views/seller/affiliates.php', 					'Affiliates'		));
$products->addPage(			new Page(						array(	array('seller', 'products', 'view')),							'views/seller/products-list.php', 				'View / Edit'		));
$products->addPage(			new Page(						array(	array('seller', 'products', 'create'),
																	array('seller', 'products', 'edit', '*')),                  	'views/seller/products-create-edit.php', 		'Create'		    ));
$products->addPage(		    new Page(						array(	array('seller', 'products', 'orders')), 						'views/seller/orders.php', 				    	'Orders'            ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'files'),
                                                                    array('seller', 'products', 'files', 'upload')),				'views/seller/files.php', 			        	'Files'	        	));
$spbox->addPage(		    new Page(						array(	array('seller', 'proxy')), 										'views/seller/proxy.php', 						'Proxy/VPN Detector'));
$spbox->addPage(		    new Page(						array(	array('seller', 'tracking')), 									'views/seller/tracking.php', 					'Sales & Tracking'  ));

$pageManager->addCategory($uncategorized);
$pageManager->addCategory($authPages);
$pageManager->addCategory($dashboard);
$pageManager->addCategory($messages);
$pageManager->addCategory($affiliates);
$pageManager->addCategory($products);
//$pageManager->addCategory($spbox);
$pageManager->addCategory($admin);

$pageManager->render(strtolower($_SERVER['REQUEST_URI']));