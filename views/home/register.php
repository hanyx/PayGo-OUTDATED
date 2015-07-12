<?php
if (isset($_POST['register']) && isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password_repeat']) /*&& isset($_POST['g-recaptcha-response'])*/) {
    try {
        NoCSRF::check('register_token', $_POST, true, 60 * 10, false);
        $uas->register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_repeat']/*, $_POST['g-recaptcha-response']*/);
    } catch (Exception $e) {}
}

$email = '';
$password = '';

if(isset($_POST['email_home']) && isset($_POST['password_home'])){
    $email = $_POST['email_home'];
    $password = $_POST['password_home'];
}

___header('Sign Up',true);
?>
    <section class="login last-section">
        <?php
        $uas->printMessages(true);
        ?>
        <h1 class="login-h1">Sign Up</h1>
        <form id="login-form" action="/seller/register" method="post" role="form" style="display: block;">
            <input type="hidden" name="register_token" value="<?php echo NoCSRF::generate('register_token'); ?>"/>
            <div class="form-group text-left">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="Username" value="">
            </div>
            <div class="form-group text-left">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" tabindex="1" class="form-control" placeholder="Email" value="<?php echo $email; ?>">
            </div>
            <div class="form-group text-left">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" value="<?php echo $password; ?>" tabindex="1" class="form-control" placeholder="Password">
            </div>
            <div class="form-group text-left">
                <label for="repeat-password">Repeat Password</label>
                <input type="password" name="password_repeat" id="repeat-password" value="<?php echo $password; ?>" tabindex="1" class="form-control" placeholder="Repeat Password">
<!--            </div>-->
<!--            <div class="form-group text-left captcha">-->
<!--                <div class='g-recaptcha' data-sitekey='--><?php //echo $config['recaptcha']['site']; ?><!--'></div>-->
            </div>
            <div class="form-group text-left">
                <div class="text-center form-text">
                    By signing up you agree to be bound by our <a href="#">Terms of Service</a> and our <a href="//www.iubenda.com/privacy-policy/954106" class="no-brand iubenda-nostyle iubenda-embed" title="Privacy Policy">Privacy Policy</a>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;">
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
___footer(true);