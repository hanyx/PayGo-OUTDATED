<?php
class SellerPageCategory extends PageCategory {
	
	public function __construct($name, $icon, $display = true, $authLevel = -1) {
		parent::__construct($name, $icon, $display, $authLevel);
	}
	
	public function checkAuth($url, UserAuthenticationSystem $uas) {
		if (!$uas->isAuthenticated()) {
			header('Location: /seller/login');
			die();
		}
		return true;
	}
	
}