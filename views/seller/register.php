<?php
if (isset($_POST['register']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password_repeat']) && isset($_POST['g-recaptcha-response'])) {
	$uas->register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_repeat'], $_POST['g-recaptcha-response']);
}

__header()
?>
	<div class="wrapper">
			<div class='row'>
				<div class='col-lg-4 col-lg-offset-4 m-t-large'>
					<section class='panel'>
						<header class='panel-heading text-center'>
							Register
						</header>
						<form action='/seller/register' method='post' class='panel-body'>
							<center><?php $uas->printMessages(); ?></center><br />
							<div class='block'>
								<label class='control-label'>Username</label>
								<input type='text' class='form-control' name='username'>
							</div>
							<div class='block'>
								<label class='control-label'>Email</label>
								<input type='email' class='form-control' name='email'>
							</div>
							<div class='block'>
								<label class='control-label'>Password</label>
								<input type='password' class='form-control' name='password'>
							</div>
							<div class='block'>
								<label class='control-label'>Confirm Password</label>
								<input type='password' class='form-control' name='password_repeat'>
							</div>
							<div class='block'>
								<div class='g-recaptcha' data-sitekey='<?php echo $config['recaptcha']['site']; ?>'></div>
							</div>
							<button name='register' type='submit' class='btn btn-info'>Register</button>
							<div class='line line-dashed'></div>
							<p class='text-muted text-center'><small>Already have an account?</small></p>
							<a href='/seller/login' class='btn btn-white btn-block'>Login</a>
						</form>
					</section>
				</div>
			</div>

<?php
__footer();