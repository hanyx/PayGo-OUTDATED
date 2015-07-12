<?php
class SellerAuthPageCategory extends PageCategory {
	
	public function __construct($name, $icon, $display = true, $authLevel = -1) {
		parent::__construct($name, $icon, $display, $authLevel);
	}
	
	public function checkAuth($url, UserAuthenticationSystem $uas) {
		if ($uas->isAuthenticated()) {
			if ($url[1] != 'logout' && !(isset($url[2]))) {
				header('Location: /seller/');
				die();
			}
		}
		return true;
	}
	
}