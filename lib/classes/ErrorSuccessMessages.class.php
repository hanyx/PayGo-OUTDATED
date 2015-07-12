<?php
abstract class ErrorSuccessMessages {
	
	private $messages;
	
	public function __construct() {
		$this->messages = array();
	}

	public function printMessages($home = false) {
		$error = array();
		$success = array();
		foreach ($this->messages as $message) {
			if ($message->isError()) {
				$error[] = $message->getMessage();
			} else {
				$success[] = $message->getMessage();
			}
        }

        if($home){
            echo '<br/>';
        }
		
		if (count($error) != 0) {
			echo '<div class=\'alert alert-danger alert-dismissable\'><button type=\'button\' class=\'close\' data-dismiss=\'alert\' aria-hidden=\'true\'>×</button>';
			foreach ($error as $message) {
				echo $message . '<br>';
			}
			echo '</div>';
		}
		
		if (count($success) != 0) {
			echo '<div class=\'alert alert-success alert-dismissable\'><button type=\'button\' class=\'close\' data-dismiss=\'alert\' aria-hidden=\'true\'>×</button>';
			foreach ($success as $message) {
				echo $message . '<br>';
			}
			echo '</div>';
		}
	}

    public function addMessage(ErrorSuccessMessage $message) {
        $this->messages[] = $message;
    }

    public function hasMessage() {
        return count($this->messages) != 0;
    }

}