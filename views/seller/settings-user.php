<?php
__header('User Settings');

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
    try {
        NoCSRF::check('password_token', $_POST, true, 60 * 10, false);
        $uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
    } catch (Exception $e) {}
}

if(isset($_POST['switch_toggle'])){
    $user = $uas->getUser();
    $user->setBigSizeBar(!$user->getBigSizeBar());
    $user->update();
    die();
}

if(isset($_POST['description'])){
    try {
        NoCSRF::check('description_token', $_POST, true, 60 * 10, false);
        $uas->getUser()->setDescription($_POST['description']);
        $uas->getUser()->update();
        $uas->addMessage(new ErrorSuccessMessage('Successfully updated your description.', false));
    } catch (Exception $e) {}
}
if(isset($_FILES['file'])){

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (preg_grep('/' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) . '/i' , $config['upload']['profilepics'])) {
        if ($_FILES['file']['size'] < 50000000) {

            $_FILES['file']['name'] = generateRandomString(5) . $_FILES['file']['name'];
            while(file_exists($config['upload']['directory'] . $_FILES['file']['name'])) {
                $_FILES['file']['name'] = generateRandomString(2) . $_FILES['file']['name'];
            }

            $uas->getUser()->setProfilePic($_FILES['file']['name']);
            $uas->getUser()->update();
            move_uploaded_file($_FILES['file']['tmp_name'], $config['upload']['directory'] . $_FILES['file']['name']);
            $uas->addMessage(new ErrorSuccessMessage('Successfully updated your profile picture.', false));
        }
    }
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
            <div class='col-md-6'>
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
                                    $link = '/u/' . $uas->getUser()->getUniqueId();
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
                            <input type="hidden" name="password_token" value="<?php echo NoCSRF::generate('password_token'); ?>"/>
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
            <div class="col-md-6">
                <div class="panel panel-default pi-panel">
                    <div class="panel-body">
                        <form class="form-horizontal" method="post" data-validate="parsley" enctype="multipart/form-data">
                            <input type="hidden" name="description_token" value="<?php echo NoCSRF::generate('description_token'); ?>"/>
                            <div class="form-group col-md-12">
                                <label>Choose profile picture</label>
                                <input type="file" name="file" id="profilePic"/><br/>
                                Allowed files types: <?php echo implode(', ', $config['upload']['profilepics']); ?>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Description</label>
                                <textarea class="form-control" name="description" style="width: 100%; max-width: 100%;"><?php echo $uas->getUser()->getDescription(); ?></textarea>
                            </div>
                            <div class="form-group col-md-12">
                                    <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
__footer();