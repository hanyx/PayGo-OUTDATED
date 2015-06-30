<?php
if (isset($_POST['register']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password_repeat']) && isset($_POST['g-recaptcha-response'])) {
	$uas->register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_repeat'], $_POST['g-recaptcha-response']);
}

___header();

$uas->printMessages();
?>
    <section class="login last-section">
        <h1 class="login-h1">Sign Up</h1>
        <form id="login-form" action="/seller/register" method="post" role="form" style="display: block;">
            <div class="form-group text-left">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="Username" value="">
            </div>
            <div class="form-group text-left">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" tabindex="1" class="form-control" placeholder="Email" value="">
            </div>
            <div class="form-group text-left">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" tabindex="1" class="form-control" placeholder="Password">
            </div>
            <div class="form-group text-left">
                <label for="repeat-password">Repeat Password</label>
                <input type="password" name="password_repeat" id="repeat-password" tabindex="1" class="form-control" placeholder="Repeat Password">
            </div>
            <div class="form-group text-left captcha">
                <div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <input type="submit" name="register" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Sign Up">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center form-text">
                            Already have an account? <a href="/seller/login" tabindex="6" class="form-links">Sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
<?php
___footer();