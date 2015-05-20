<?php
if ($url[1] == 'logout') {
	if ($uas->isAuthenticated()) {
		$uas->logout();
	} else {
		header('Location: /seller/login');
		die();
	}
}

if (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['g-recaptcha-response'])) {
	if ($uas->login($_POST['username'], $_POST['password'], $_POST['g-recaptcha-response'])) {
		header('Location: /seller/');
		die();
	}
}

if (count($url) == 4 && ($url[2] == 'activate' || $url[2] == 'update')) {
	$tfr = new TwoFactorRequest();

	if ($tfr->readByToken($url[3])) {
		if ($tfr->getAction() == TwoFactorRequestAction::ACTIVATE) {
			$tfr->process();
		}
		
		if ($tfr->getAction() == TwoFactorRequestAction::UPDATEPASSWORD) {
			$tfr->process();
			header('Location: /seller/login?update=true');
			die();
		}
	}
}

if (isset($_GET['update'])) {
	$uas->addMessage(new ErrorSuccessMessage('Password updated', false));
}

include_once('header.php');
?>
	<section id='content'>
		<div class='main padder'>
			<div class='row'>
				<div class='col-lg-4 col-lg-offset-4 m-t-large'>
					<section class='panel'>
						<header class='panel-heading text-center'>
							Login
						</header>
						<form action='/seller/login' class='panel-body' method='post'>
							<center>
								<?php 
								$uas->printMessages(); 
								if (isset($tfr)) {
									$tfr->printMessages();
								}
								?>
							</center><br />
							<div class='block'>
								<label class='control-label'>Username</label>
								<input type='text' class='form-control' name='username'>
							</div>
							<div class='block'>
								<label class='control-label'>Password</label>
								<input type='password' class='form-control' name='password'>
							</div>
							<div class='block'>
								<div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
							</div>
							<a href='/seller/reset' class='pull-right m-t-mini'><small>Forgot password?</small></a>
							<button type='submit' class='btn btn-info' name='login'>Sign in</button>
							<div class='line line-dashed'></div>
							<p class='text-muted text-center'><small>Do not have an account?</small></p>
							<a href='/seller/register' class='btn btn-white btn-block'>Register</a>
						</form>
					</section>
				</div>
			</div>
		</div>
	</section>
<?php
include_once('footer.php');