<?php
class SellerPageCategory extends PageCategory {
	
	public function __construct($name, $icon, $display = true, $authLevel = -1) {
		parent::__construct($name, $icon, $display, $authLevel);
	}
	
	public function checkAuth($url, UserAuthenticationSystem $uas, $soft = false) {
		if (!$uas->isAuthenticated() && !$soft) {
            header('Location: /seller/login');
            die();
		}

		return parent::checkAuth($url, $uas, $soft);
	}
	
}