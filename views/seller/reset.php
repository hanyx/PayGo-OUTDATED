<?php
if (isset($_POST['reset']) && isset($_POST['username']) && isset($_POST['g-recaptcha-response'])) {
	$uas->processReset($_POST['username'], $_POST['g-recaptcha-response']);
}

$displaySecondaryForm = false;

if (count($url) == 3) {
	$tfr = new TwoFactorRequest();

	if ($tfr->readByToken($url[2])) {
		if (!$tfr->getUsed() && $tfr->getAction() == TwoFactorRequestAction::RESET) {
			$displaySecondaryForm = true;
		}
	}
}

if (isset($_POST['reset']) && isset($_POST['password']) && isset($_POST['password_repeat']) && isset($_POST['token'])) {
	$uas->reset($_POST['password'], $_POST['password_repeat'], $_POST['token']);
}

__header('Reset Password');
?>
	<section id='content'>
		<div class='main padder'>
			<div class='row'>
				<div class='col-lg-4 col-lg-offset-4 m-t-large'>
					<section class='panel'>
						<header class='panel-heading text-center'>
							Reset Password
						</header>
						<form action='/seller/reset' class='panel-body' method='post'>
							<center><?php $uas->printMessages(); ?></center><br />
							<?php 
							if ($displaySecondaryForm) {
							?>
								<div class='block'>
									<label class='control-label'>New Password</label>
									<input type='password' class='form-control' name='password'>
								</div>
								<div class='block'>
									<label class='control-label'>Confirm New Password</label>
									<input type='password' class='form-control' name='password_repeat'>
								</div>
								<input type='hidden' name='token' value='<?php echo $url[2]; ?>'>
							<?php
							} else {
								?>
								<div class='block'>
									<label class='control-label'>Email</label>
									<input type='text' class='form-control' name='username'>
								</div>
								<div class='block'>
									<div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
								</div>
							<?php
							}
							?>
							<button type='submit' class='btn btn-info' name='reset'>Reset</button>
							<div class='line line-dashed'></div>
							<p class='text-muted text-center'><small>Remembered your password?</small></p>
							<a href='/seller/login' class='btn btn-white btn-block'>Login</a>
						</form>
					</section>
				</div>
			</div>
		</div>
	</section>
<?php
__footer();