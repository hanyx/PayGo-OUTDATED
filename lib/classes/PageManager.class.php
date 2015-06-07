<?php
class PageManager {
	
	private $categories;
	private $four04Page;
	private $permsPage;
	private $uas;
    private $currentPage;
	
	public function __construct($uas) {
		$this->uas = $uas;
		$this->categories = array();
	}
	
	public function addCategory(PageCategory $page) {
		$this->categories[] = $page;
	}
	
	public function getCategories() {
		return $this->categories;
	}
	
	public function set404Page(Page $page) {
		$this->four04Page = $page;
	}
	
	public function setPermsPage(Page $page) {
		$this->permsPage = $page;
	}
	
	public function render($uri) {
		$url = array();

		$path = $uri;

		if (strpos($path, "?") !== false) {
			$path = substr($path, 0, strpos($path, "?"));
		}

		while (!(pathinfo($path)['dirname'] == "\\" || pathinfo($path)['dirname'] == "/")) {
			$inf = pathinfo($path);
			$url[] = $inf['filename'] . (array_key_exists("extension", $inf) ? ("." . $inf['extension']) : "");
			$path = $inf['dirname'];
		}

		$url[] = pathinfo($path)['filename'] . (array_key_exists("extension", pathinfo($path)) ? ("." . pathinfo($path)['extension']) : "");

		$url = array_reverse($url);

		foreach ($this->categories as $category) {
			foreach ($category->getPages() as $page) {
				if ($page->urlMatch($url)) {
					if (!$category->checkAuth($url, $this->uas)) {
						if (isset($this->permsPage)) {
							$this->currentPage = $this->permsPage;
						}
						break 2;
					}
					$this->currentPage = $page;
					$page->setCurrent();
					$category->setCurrent();
					break 2;
				}
			}
		}

		if (!isset($this->currentPage) && isset($this->four04Page)) {
			$this->currentPage = $this->four04Page;
		}
		
		if (isset($this->currentPage)) {
			$this->currentPage->render($this->uas, $url);
		}
	}

    public function getCurrentPage() {
        return $this->currentPage;
    }

}