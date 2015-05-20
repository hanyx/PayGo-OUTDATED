<?php
class ErrorSuccessMessage {
	
	private $message;
	private $error;
	
	public function __construct($message, $error = true) {
		$this->message = $message;
		$this->error = $error;
	}

	public function getMessage() {
		return $this->message;
	}

	public function isError() {
		return $this->error;
	}

}