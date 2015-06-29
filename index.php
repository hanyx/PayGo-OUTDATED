<?php
date_default_timezone_set('America/Chicago');

require_once('lib/functions.php');



$uas = new UserAuthenticationSystem();
$pageManager = new PageManager($uas);

$uncategorized = 	new PageCategory(			'',				'', 			        false);
$authPages = 		new SellerAuthPageCategory(	'', 			'', 			        false);
$dashboard = 		new SellerPageCategory(		'Dashboard', 	'fa-eye',	            true, UserAccountType::PREMIUM);
$messages = 		new SellerPageCategory(		'Messages', 	'fa fa-envelope-o',     true, UserAccountType::PREMIUM);
$affiliates = 		new SellerPageCategory(		'Affiliates', 	'fa-user', 		        true, UserAccountType::PREMIUM);
$products =			new SellerPageCategory(		'Products', 	'fa-list-alt',  	    true, UserAccountType::PREMIUM);
$coupons =          new SellerPageCategory(     'Coupons',      'fa-ticket',            true, UserAccountType::PREMIUM);
$admin = 			new SellerPageCategory(		'Admin', 		'fa-wrench', 	        true, UserAccountType::ADMIN);

$uncategorized->addPage(	new Page(						array(	array('')), 						    				    	'views/home.php',                               '',                                             false, false, true  ));
$uncategorized->addPage(	new Page(						array(	array('robots.txt')), 						    		    	'views/robots.php',                             ''                                                                  ));
$uncategorized->addPage(	new Page(						array(	array('sitemap.xml')), 						    		    	'views/sitemap.php',                            ''                                                                  ));
$pageManager->set404Page(	new Page(						array(	array('')), 													'views/404.php', 								'404'					                                            ));
$pageManager->setPermsPage(	new Page(						array(	array('')), 													'views/nopermission.php', 						'No Permission'                                                     ));
$uncategorized->addPage(	new Page(						array(	array('v', '*'),
    																array('v', '*', 'a', '*'),
                                                                    array('v', '*', '*'),
    														        array('v', '*', 'buy')), 							        	'views/product.php', 							'',                                             false, true			));
$uncategorized->addPage(	new Page(						array(	array('ipn', 'paypal'),
                                                                    array('ipn', 'coinpayments')), 									'views/ipn.php', 								''					                                                ));
$uncategorized->addPage(	new Page(						array(	array('download', '*')), 										'views/download.php', 							''					                                                ));
$uncategorized->addPage(	new Page(						array(	array('u', '*')), 												'views/user.php',                               '',                                             false, true         ));
$authPages->addPage(		new Page(						array(	array('seller', 'login'),
																	array('seller', 'login', 'activate', '*'),
																	array('seller', 'login', 'update', '*'),
																	array('seller', 'logout')),										'views/seller/login.php',                       'Login',                                        false, false, true  ));
$authPages->addPage(		new Page(						array(	array('seller', 'register')),									'views/seller/register.php',                    'Register',                                     false, false, true  ));
$authPages->addPage(		new Page(						array(	array('seller', 'reset'), 
																	array('seller', 'reset', '*')),									'views/seller/reset.php',                       'Reset Password',                               false, false, true  ));
$dashboard->addPage(		new Page(						array(	array('seller'),
                                                                    array('seller', 'chart')), 										'views/seller/home.php', 						'Dashboard',                'Subtext',          false, false, true  ));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings')),				                	'views/seller/settings-user.php', 				'User Settings',            'Subtext'                               ));
$dashboard->addPage(		new Page(						array(	array('seller', 'settings', 'payments')),		               	'views/seller/settings-payment.php', 			'Payment Settings',         'Subtext'                               ));
$messages->addPage(			new SellerMessagesInboxPage(	array(	array('seller', 'messages', 'inbox'),
																	array('seller', 'messages', 'inbox', 'view', '*'),
																	array('seller', 'messages')),									'views/seller/messages-inbox.php', 				'Inbox',			    	'Subtext'                               ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'sent'),
																	array('seller', 'messages', 'sent', 'view', '*')),				'views/seller/messages-sent.php', 				'Sent',				        'Subtext'                               ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'product-delivery'),
																	array('seller', 'messages', 'product-delivery', 'view', '*')), 	'views/seller/messages-product-delivery.php', 	'Product Delivery',         'Subtext'                               ));
$messages->addPage(			new Page(						array(	array('seller', 'messages', 'compose'),
																	array('seller', 'messages', 'reply', '*')), 					'views/seller/messages-compose.php', 			'Compose Message',			'Subtext',         true                 ));
$affiliates->addPage(		new Page(						array(	array('seller', 'affiliates'),
																	array('seller', 'affiliates', 'pay', '*')), 					'views/seller/affiliates.php', 					'Affiliates',		        'Subtext'                               ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'view'),
                                                                    array('seller', "products", 'view', 'delete', '*')),	        'views/seller/products-list.php', 				'View / Edit Products',		'Subtext'                               ));
$products->addPage(		    new Page(						array(	array('seller', 'products', 'orders')), 						'views/seller/orders.php', 				    	'Orders',                   'Subtext'                               ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'files'),
                                                                    array('seller', 'products', 'files', 'upload')),				'views/seller/files.php', 			        	'View / Upload Files',	    'Subtext'                               ));
$products->addPage(			new Page(						array(	array('seller', 'products', 'create'),
                                                                    array('seller', 'products', 'edit', '*')),                      'views/seller/products-create-edit.php', 		'Create Product',		    'Subtext',         true                 ));
$coupons->addPage(          new Page(                       array(  array('seller', 'coupons', 'view')),                            'views/seller/coupons-list.php',                'View / Edit Coupons',      'Subtext'                               ));
$coupons->addPage(          new Page(                       array(  array('seller', 'coupons', 'create'),
                                                                    array('seller', 'coupons', 'edit', '*')),                       'views/seller/coupons-create-edit.php',         'Create Coupon',            'Subtext',          true                ));
$admin->addPage(            new Page(                       array(  array('seller', 'admin', 'newsletter')),                        'views/seller/admin-newsletter.php',            'Newsletter',               'Subtext'                               ));

$pageManager->addCategory($uncategorized);
$pageManager->addCategory($authPages);
$pageManager->addCategory($dashboard);
$pageManager->addCategory($messages);
$pageManager->addCategory($affiliates);
$pageManager->addCategory($products);
$pageManager->addCategory($coupons);
$pageManager->addCategory($admin);

$pageManager->render(strtolower($_SERVER['REQUEST_URI']));