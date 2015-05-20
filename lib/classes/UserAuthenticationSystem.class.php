<?php
class UserAuthenticationSystem extends ErrorSuccessMessages {
	
	private $authenticated;
	private $user;
	
	public function __construct() {
		parent::__construct();
		
		session_start();

		$this->authenticated = false;
		
		if (isset($_SESSION['user_id']) && isset($_SESSION['session'])) {
			$user = new User();

			if (!$user->read($_SESSION['user_id'])) {
				session_destroy();
			} else if ($user->getSession() != $_SESSION['session']) {
				session_destroy();
			} else {
				$this->user = $user;
				$this->authenticated = true;
			}
		}
	}
	
	public function login($username, $password, $captcha) {
		if ($this->authenticated) {
			return false;
		}
		
		if (!validateReCaptcha($captcha)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Captcha'));
			return false;
		}
		
		$user = new User();

		if (!$user->readByUsername($username) && !$user->readByEmail($username)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Username or Email'));
			return false;
		}
		
		if (!$user->checkPassword($password)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Password'));
			return false;
		}
		
		if (!$user->getActive()) {
			$this->addMessage(new ErrorSuccessMessage('Account not activated. Please check your email to activate your account'));
			return false;
		}
		
		$this->user = $user;
		$this->user->setLastLogin(getRealIp());
		$this->user->update();
		
		$this->authenticated = true;
		
		$_SESSION['user_id'] = $this->user->getId();
		$_SESSION['session'] = $this->user->getSession();

		return true;
	}
	
	public function logout() {
		if ($this->authenticated) {
			session_destroy();
			
			$this->addMessage(new ErrorSuccessMessage('You have been logged out', false));
			$this->authenticated = false;
			$this->user = null;
			
			return true;
		}
		return false;
	}
	
	public function register($username, $email, $password, $password2, $captcha) {	
		if ($this->authenticated) {
			return false;
		}
		
		$username = strtolower($username);
		
		if (!validateReCaptcha($captcha)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Captcha'));
			return false;
		}
		
		if (!ctype_alnum($username)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Username. Only alpha-numerical characters allowed'));
			return false;
		}
		
		if (strlen($username) < 3 || strlen($username) > 30) {
			$this->addMessage(new ErrorSuccessMessage('Username must be at least 3 characters and under 30 characters in length'));
			return false;
		}
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Email'));
			return false;
		}
		
		if ($password != $password2) {
			$this->addMessage(new ErrorSuccessMessage('Passwords don\'t Match'));
			return false;
		}
		
		$user = new User();

		if ($user->readByUsername($username)) {
			$this->addMessage(new ErrorSuccessMessage('Username is already taken'));
			return false;
		}

		if ($user->readByEmail($email)) {
			$this->addMessage(new ErrorSuccessMessage('Email is already registered'));
			return false;
		}
		
		$user = new User();

        $user->setUsername($username);
        $user->setPassword($password);
        $user->setEmail($email);

        $user->create();

		$tfr = new TwoFactorRequest();

        $tfr->setUserId($user->getId());
        $tfr->setAction(TwoFactorRequestAction::ACTIVATE);

		$tfr->create();

		$this->addMessage(new ErrorSuccessMessage('Account registered. Please check your email to activate your account', false));
		
		$mailer = new Mailer();
		
		$mailer->sendTemplate(EmailTemplate::ACTIVATE, $user->getEmail(), $user->getUsername(), $tfr->getToken());
				
		return true;
	}
	
	public function processReset($email, $captcha) {
		if ($this->authenticated) {
			return false;
		}
		
		if (!validateReCaptcha($captcha)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Captcha'));
			return false;
		}
		
		$user = new User();

		if (!$user->readByEmail($email)) {
			$this->addMessage(new ErrorSuccessMessage('Invalid Email'));
			return false;
		}
		
		$tfr = new TwoFactorRequest();

        $tfr->setUserId($user->getId());
        $tfr->setAction(TwoFactorRequestAction::RESET);

		$tfr->create();
		
		$this->addMessage(new ErrorSuccessMessage('We have sent a password form to the email on file', false));
		
		$mailer = new Mailer();

        $mailer->sendTemplate(EmailTemplate::RESET, $user->getEmail(), $user->getUsername(), $tfr->getToken(), getRealIp());
		
		return true;
	}
	
	public function reset($password, $password2, $token) {
		$tfr = new TwoFactorRequest();

		if (!$tfr->readByToken($token)) {
			return false;
		}
		
		if ($tfr->getUsed()) {
			return false;
		}
		
		if ($tfr->getAction() != TwoFactorRequestAction::RESET) {
			return false;
		}

		if ($password != $password2) {
			$this->addMessage(new ErrorSuccessMessage('Passwords don\'t Match'));
			return false;
		}

		$user = new User();

		$user->read($tfr->getUserId());
		$user->setPassword($password);
		$user->update();
		
		$tfr->process();
		
		$this->addMessage(new ErrorSuccessMessage('Password updated', false));
		
		return true;
	}
	
	public function processUpdatePassword($oldPassword, $password, $password2) {
		if (!$this->authenticated) {
			return false;
		}
		
		if ($password != $password2) {
			$this->addMessage(new ErrorSuccessMessage('New passwords don\'t Match'));
			return false;
		}
		
		if (!$this->user->checkPassword($oldPassword)) {
			$this->addMessage(new ErrorSuccessMessage('Old password is incorrect'));
			return false;
		}
		
		$tfr = new TwoFactorRequest();

        $tfr->setUserId($this->user->getId());
        $tfr->setAction(TwoFactorRequestAction::UPDATEPASSWORD);
        $tfr->setData(password_hash($password, 1));

		$tfr->create();
		
		$this->addMessage(new ErrorSuccessMessage('Please check your email to update your password', false));
		
		$mailer = new Mailer();
		
		$mailer->sendTemplate(EmailTemplate::UPDATEPASSWORD, $this->user->getEmail(), $this->user->getUsername(), $tfr->getToken(), getRealIp());
		
		return true;
	}
	
	public function processUpdatePaymentDetails($paypal, $bitcoin, $litecoin, $omnicoin) {
		if (!$this->authenticated) {
			return false;
		}
		
		if ($paypal != '' && !filter_var($paypal, FILTER_VALIDATE_EMAIL)) {
			$this->addMessage(new ErrorSuccessMessage('PayPal email is invalid'));
			return false;
		}
		
		if ($bitcoin != '' && !ctype_alnum($bitcoin)) {
			$this->addMessage(new ErrorSuccessMessage('Bitcoin address is invalid'));
			return false;
		}
		
		if ($litecoin != '' && !ctype_alnum($litecoin)) {
			$this->addMessage(new ErrorSuccessMessage('Litecoin address is invalid'));
			return false;
		}
		
		if ($omnicoin != '' && !ctype_alnum($omnicoin)) {
			$this->addMessage(new ErrorSuccessMessage('Omnicoin address is invalid'));
			return false;
		}
		
		$tfr = new TwoFactorRequest();

        $tfr->setUserId($this->user->getId());
        $tfr->setAction(TwoFactorRequestAction::UPDATEPAYMENTDETAILS);
        $tfr->setData(implode(',', array($paypal, $bitcoin, $litecoin, $omnicoin)));

		$tfr->create();
		
		$this->addMessage(new ErrorSuccessMessage('Please check your email to update your payment details', false));
		
		$mailer = new Mailer();
		
		$mailer->sendTemplate(EmailTemplate::UPDATEPAYMENTDETAILS, $this->user->getEmail(), $this->user->getUsername(), $tfr->getToken(), getRealIp());
		
		return true;
	}
	
	public function isAuthenticated() {
		return $this->authenticated;
	}
	
	public function getUser() {
		return $this->user;
	}

}