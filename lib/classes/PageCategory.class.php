<?php
class PageCategory {
	
	private $name;
	private $icon;
	private $display;
	private $authLevel;
	private $current;
	private $pages;
	
	public function __construct($name, $icon, $display = true, $authLevel = -1) {
		$this->name = $name;
		$this->icon = $icon;
		$this->display = $display;
		$this->authLevel = $authLevel;
		$this->pages = array();
	}
	
	public function getPages() {
		return $this->pages;
	}
	
	public function addPage(Page $page) {
		$this->pages[] = $page;
	}
	
	public function checkAuth($url, UserAuthenticationSystem $uas) {
		if ($this->authLevel != -1) {
			if (!$uas->isAuthenticated()) {
				return false;
			} else {
				if ($uas->getUser()->getAccountType() < $this->authLevel) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function isHidden() {
		return !$this->display;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getIcon() {
		return $this->icon;
	}
	
	public function isCurrent() {
		return $this->current;
	}
	
	public function setCurrent() {
		$this->current = true;
	}
	
}