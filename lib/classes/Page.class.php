<?php
class Page {
	
	private $url;
	private $file;
	private $name;
	private $current;
    private $noAuth;
	
	public function __construct($url, $file, $name = "", $noAuth = false) {
		$this->url = $url;
		$this->file = $file;
		$this->name = $name;
        $this->noAuth = $noAuth;
	}
	
	public function urlMatch($url) {
		foreach ($this->url as $urls) {
			$bad = false;
			for ($x = 0; $x < count($urls) || $x < count($url); $x++) {
				if (count($urls) > $x && ((count($url) > $x && $urls[$x] == $url[$x]) || $urls[$x] == "*")) {
					continue;
				} else {
					$bad = true;
					break;
				}
			}
			if ($bad) {
				continue;
			}
			return true;
		}
		return false;
	}
	
	public function render($uas, $url) {		
		global $config;
		global $pageManager;
		
		include_once($this->file);
	}
	
	public function isCurrentPage() {
		return $this->current;
	}
	
	public function setCurrent() {
		$this->current = true;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getLink() {
		$url = "";
		
		foreach ($this->url[0] as $part) {
			$url .= "/" . $part;
		}
		
		return $url;
	}
	
	public function getAlerts(User $user) {
		return 0;
	}

    public function noAuth() {
        return $this->noAuth;
    }
	
}