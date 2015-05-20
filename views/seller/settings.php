<?php
include_once('header.php');

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
	$uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
}

if (isset($_POST['update-payment-details']) && isset($_POST['paypal']) && isset($_POST['bitcoin']) && isset($_POST['litecoin']) && isset($_POST['omnicoin'])) {
	$uas->processUpdatePaymentDetails($_POST['paypal'], $_POST['bitcoin'], $_POST['litecoin'], $_POST['omnicoin']);
}

if (count($url) == 4 && ($url[2] == 'update')) {
	$tfr = new TwoFactorRequest();

	if ($tfr->readByToken($url[3])) {
		if ($tfr->getAction() == TwoFactorRequestAction::UPDATEPAYMENTDETAILS) {
			$tfr->process();
			$uas->getUser()->read($uas->getUser()->getId());
		}
	}
}
?>
	<section id='content'>
		<section class='main padder'>
			<div class='clearfix'>
				<h4><i class='fa fa-gear'></i> Settings</h4>
				<?php 
				$uas->printMessages(); 
				if (isset($tfr)) {
					$tfr->printMessages();
				}
				?>
			</div>
			<div class='row'>
				<div class='col-sm-6'>
					<section class='panel'>
						<div class='panel-body'>
							<form class='form-horizontal' method='get' data-validate='parsley'>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Username</label>
									<div class='col-lg-8 control-label' style='text-align: left;'>
										<?php echo $uas->getUser()->getUsername(); ?>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Email</label>
									<div class='col-lg-8 control-label' style='text-align: left;'>
										<?php echo $uas->getUser()->getEmail(); ?>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>My URL</label>
									<div class='col-lg-8 control-label' style='text-align: left;'>
										<?php
										$link = $config['url']['protocol'] . $config['url']['domain'] . '/u/' . $uas->getUser()->getUsername();
										echo '<a href=\'' . $link . '\'>' . $link . '</a>';
										?>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Last Login</label>
									<div class='col-lg-8 control-label' style='text-align: left;'>
										<?php echo $uas->getUser()->getLastLoginTimestamp() . ' CST from ' . $uas->getUser()->getLastLoginIp(); ?>
									</div>
								</div>
							</form>
						</div>
					</section>
					<section class='panel'>
						<div class='panel-body'>
							<form class='form-horizontal' method='post' data-validate='parsley'>			
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Old Password</label>
									<div class='col-lg-8'>
										<input type='password' name='password-old' class='form-control' required=''>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>New Password</label>
									<div class='col-lg-8'>
										<input type='password' name='password' class='form-control' required=''>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Confirm New Password</label>
									<div class='col-lg-8'>
										<input type='password' name='password-confirm' class='form-control' required=''>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<div class='col-lg-9 col-lg-offset-3'>
										<button type='submit' name='update-password' class='btn btn-primary'>Update Password</button>
									</div>
								</div>
							</form>
						</div>
					</section>
				</div>
				<div class='col-sm-6'>
					<section class='panel'>
						<div class='panel-body'>
							<form class='form-horizontal' method='post' data-validate='parsley'>			
								<div class='form-group'>
									<label class='col-lg-3 control-label'>PayPal Email</label>
									<div class='col-lg-8'>
										<input type='email' name='paypal' class='form-control' placeholder='(e.g. john.doe@gmail.com)' value='<?php echo $uas->getUser()->getPaypal(); ?>'>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Bitcoin Address</label>
									<div class='col-lg-8'>
										<input type='text' name='bitcoin' class='form-control' placeholder='(e.g. 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa)' value='<?php echo $uas->getUser()->getBitcoin(); ?>'>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Litecoin Address</label>
									<div class='col-lg-8'>
										<input type='text' name='litecoin' class='form-control' placeholder='(e.g. Le6X4DDUchAD5GmEmbbnektzUZCQ3JpsUC)' value='<?php echo $uas->getUser()->getLitecoin(); ?>'>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>
								<div class='form-group'>
									<label class='col-lg-3 control-label'>Omnicoin Address</label>
									<div class='col-lg-8'>
										<input type='text' name='omnicoin' class='form-control' placeholder='(e.g. ocVUFe8YF2bPyocLFMhNtCF5zDjSQFKJVi)' value='<?php echo $uas->getUser()->getOmnicoin(); ?>'>
										<div class='line line-dashed m-t-large'></div>
									</div>
								</div>

								<div class='form-group'>
									<div class='col-lg-9 col-lg-offset-3'>
										<button type='submit' name='update-payment-details' class='btn btn-primary'>Update Payment Settings</button>
									</div>
								</div>
							</form>
						</div>
					</section>
				</div>
			</div>
		</section>
	</section>
<?php
include_once('footer.php');