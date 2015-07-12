<?php
if ($url[1] == 'logout') {
	if ($uas->isAuthenticated()) {
		$uas->logout();
	} else {
		header('Location: /seller/login');
		die();
	}
}

if (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
    try {
        NoCSRF::check('login_token', $_POST, true, 60 * 10, false);
        if ($uas->login($_POST['username'], $_POST['password'], isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '')) {
            header('Location: /seller/');
            die();
        }
    } catch (Exception $e){

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

___header('Login', true);
?>
    <section class="login last-section">
        <?php
        $uas->printMessages(true);
        if (isset($tfr)) {
            $tfr->printMessages();
        }
        ?>
        <h1 class="login-h1">Sign In</h1>
        <form id="login-form" action="/seller/login" method="post" role="form" style="display: block;">
            <input type="hidden" name="login_token" value="<?php echo NoCSRF::generate('login_token'); ?>"/>
            <div class="form-group text-left">
                <label for="email">Email</label>
                <input type="text" name="username" id="email" tabindex="1" class="form-control" placeholder="Email" value="">
            </div>
            <div class="form-group text-left">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password">
            </div>
            <?php if ($uas->getRequireCaptcha()) { ?>
                <div class="form-group text-left captcha">
                    <div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
                </div>
            <?php } ?>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <input type="submit" name="login" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Sign In">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <a href="/seller/reset" tabindex="5" class="form-links">Forgot Password?</a>
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
___footer(true);