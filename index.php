<?php
date_default_timezone_set('America/Chicago');

require_once('lib/functions.php');



$uas = new UserAuthenticationSystem();
$pageManager = new PageManager($uas);

$uncategorized = 	new PageCategory(			'',				'', 			    false);
$authPages = 		new SellerAuthPageCategory(	'', 			'', 			    false);
$dashboard = 		new SellerPageCategory(		'Dashboard', 	'fa-eye',	        true, 0);
$messages = 		new PageCategory(			'Messages', 	'fa fa-envelope-o', true, 0);
$affiliates = 		new PageCategory(			'Affiliates', 	'fa-user', 		    true, 0);
$products =			new PageCategory(			'Products', 	'fa-list-alt',  	true, 0);
$coupons =          new PageCategory(           'Coupons',      'fa-ticket',        true,   0);
$admin = 			new PageCategory(			'Admin', 		'fa-wrench', 	    true, 1);

$uncategorized->addPage(	new Page(						array(	array('')), 						    				    	'views/home.php',                              '',                   false, true        ));
//$uncategorized->addPage(	new Page(						array(	array('pricing')), 						    					'views/pricing.php',                           '',                   false, true        ));
//$uncategorized->addPage(	new Page(						array(	array('built')), 						    					'views/built.php',                             '',                   false, true        ));
$uncategorized->addPage(	new Page(						array(	array('robots.txt')), 						    		    	'views/robots.php'                                                                      ));
$uncategorized->addPage(	new Page(						array(	array('sitemap.xml')), 						    		    	'views/sitemap.php'                                                                     ));

$pageManager->set404Page(	new Page(						array(	array('')), 													'views/404.php' 													                    ));
$pageManager->setPermsPage(	new Page(						array(	array('')), 													'views/nopermission.php' 						                                        ));

$uncategorized->addPage(	new Page(						array(	array('v', '*'),
    																array('v', '*', 'a', '*'),
                                                                    array('v', '*', '*'),
    														        array('v', '*', 'buy')), 							        	'views/product.php', 							'',                   true			    ));
$uncategorized->addPage(	new Page(						array(	array('ipn', 'paypal'),
                                                                    array('ipn', 'coinpayments')), 									'views/ipn.php' 													                    ));
$uncategorized->addPage(	new Page(						array(	array('download', '*')), 										'views/download.php' 												                    ));
$uncategorized->addPage(	new Page(						array(	array('u', '*')), 												'views/user.php',                               '',                   true              ));

$authPages->addPage(		new Page(						array(	array('seller', 'login'),
																	array('seller', 'login', 'activate', '*'),
																	array('seller', 'login', 'update', '*'),
																	array('seller', 'logout')),										'views/seller/login.php',                       '',                   false, true       ));
$authPages->addPage(		new Page(						array(	array('seller', 'register')),									'views/seller/register.php',                    '',                   false, true       ));
$authPages->addPage(		new Page(						array(	array('seller', 'reset'), 
																	array('seller', 'reset', '*')),									'views/seller/reset.php',                       '',                   false, true       ));
$dashboard->addPage(		new Page(						array(	array('seller'),
                                                                    array('seller', 'chart')), 										'views/seller/home.php', 						'Dashboard',          false, true       ));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings')),				                	'views/seller/settings-user.php', 				'User Settings'		                    ));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings', 'payments')),		               	'views/seller/settings-payment.php', 			'Payment Settings'	                    ));
$messages->addPage(			new SellerMessagesInboxPage(	array(	array('seller', 'messages', 'inbox'),
																	array('seller', 'messages', 'inbox', 'view', '*'),
																	array('seller', 'messages', 'inbox', 'delete', '*'),
																	array('seller', 'messages')),									'views/seller/messages-inbox.php', 				'Inbox'				                    ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'sent'),
																	array('seller', 'messages', 'sent', 'view', '*')),				'views/seller/messages-sent.php', 				'Sent'				                    ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'product-delivery'),
																	array('seller', 'messages', 'product-delivery', 'view', '*')), 	'views/seller/messages-product-delivery.php', 	'Product Delivery'	                    ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'compose'),
																	array('seller', 'messages', 'reply', '*')), 					'views/seller/messages-compose.php', 			'Compose'			                    ));
$affiliates->addPage(		new Page(						array(	array('seller', 'affiliates'),
																	array('seller', 'affiliates', 'pay', '*')), 					'views/seller/affiliates.php', 					'Affiliates'		                    ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'view'),
                                                                    array('seller', "products", 'view', 'delete', '*')),	        'views/seller/products-list.php', 				'View / Edit'		                    ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'create'),
																	array('seller', 'products', 'edit', '*')),                      'views/seller/products-create-edit.php', 		'Create'		                        ));
$products->addPage(		    new Page(						array(	array('seller', 'products', 'orders')), 						'views/seller/orders.php', 				    	'Orders'                                ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'files'),
                                                                    array('seller', 'products', 'files', 'upload')),				'views/seller/files.php', 			        	'Files'	        	                    ));

$coupons->addPage(          new Page(                       array(  array('seller', 'coupons', 'view')),                            'views/seller/coupons-list.php',                'View / Edit'                           ));
$coupons->addPage(          new Page(                       array(  array('seller', 'coupons', 'create'),
                                                                    array('seller', 'coupons', 'edit', '*')),                       'views/seller/coupons-create-edit.php',         'Create'                                ));

$pageManager->addCategory($uncategorized);
$pageManager->addCategory($authPages);
$pageManager->addCategory($dashboard);
$pageManager->addCategory($messages);
$pageManager->addCategory($affiliates);
$pageManager->addCategory($products);
$pageManager->addCategory($coupons);
$pageManager->addCategory($admin);

$pageManager->render(strtolower($_SERVER['REQUEST_URI']));