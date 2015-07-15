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

___header('Reset', true, false, 'Reset your password on PayIvy to continue selling');
?>
    <section class="login last-section">
        <?php
        $uas->printMessages();
        ?>
        <h1 class="login-h1">Forgot Password</h1>
        <form id="login-form" action="/seller/reset" method="post" role="form" style="display: block;">
            <?php
            if ($displaySecondaryForm) {
                ?>
                <div class="form-group text-left">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" tabindex="1" class="form-control" placeholder="Password">
                </div>
                <div class="form-group text-left">
                    <label for="repeat-password">Repeat Password</label>
                    <input type="password" name="password_repeat" id="repeat-password" tabindex="1" class="form-control" placeholder="Repeat Password">
                </div>
                <input type='hidden' name='token' value='<?php echo $url[2]; ?>'>
                <?php
            } else {
                ?>
                <div class="form-group text-left">
                    <label for="email">Email</label>
                    <input type="text" name="username" id="email" tabindex="1" class="form-control" placeholder="Email">
                </div>
                <div class="form-group text-left captcha">
                    <div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
                </div>
                <?php
            }
            ?>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <input type="submit" name="reset" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Recover Password">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center form-text">
                            Remember you password? <a href="/seller/login" tabindex="5" class="form-links">Sign In</a>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="text-center form-text">
                            Don't have an account? <a href="/seller/register" tabindex="6" class="form-links">Sign Up</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
<?php
___footer();