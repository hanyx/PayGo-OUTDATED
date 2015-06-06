<?php
include_once('header.php');

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
	$uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
}

if(isset($_POST['switch_toggle'])){
    $user = $uas->getUser();
    $user->setBigSizeBar(!$user->getBigSizeBar());
    $user->update();
    die();
}
?>
	<section class="wrapper">
        <div class='clearfix'>
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
        </div>
    </section>
<?php
include_once('footer.php');