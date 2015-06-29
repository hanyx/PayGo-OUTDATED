<?php
abstract class ErrorSuccessMessages {
	
	private $messages;
	
	public function __construct() {
		$this->messages = array();
	}

	public function printMessages() {
		$error = array();
		$success = array();
		foreach ($this->messages as $message) {
			if ($message->isError()) {
				$error[] = $message->getMessage();
			} else {
				$success[] = $message->getMessage();
			}
		}
		
		if (count($error) != 0) {
            echo '<div class="panel panel-danger notification"><div class="panel-body"><button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			foreach ($error as $message) {
				echo $message . '<br>';
			}
            echo '</div></div>';
		}
		
		if (count($success) != 0) {
            echo '<div class="panel panel-success notification"><div class="panel-body"><button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			foreach ($success as $message) {
				echo $message . '<br>';
			}
			echo '</div></div>';
		}
	}

    public function addMessage(ErrorSuccessMessage $message) {
        $this->messages[] = $message;
    }

    public function hasMessage() {
        return count($this->messages) != 0;
    }

}