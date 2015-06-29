<?php
__header();

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
	$uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
}
?>
    <?php
    $uas->printMessages();
    if (isset($tfr)) {
        $tfr->printMessages();
    }
    ?>
    <div class="row">
        <div class="container-fluid">
            <div class="col-md-6">
                <div class="panel panel-default pi-panel ">
                    <div class="panel-body">
                        <form class="form-horizontal" method="get" data-validate="parsley">
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Username</label>
                                <div class="col-lg-8 control-label muted" style="text-align: left;font-weight:400;">
                                    <?php echo $uas->getUser()->getUsername(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Email</label>
                                <div class="col-lg-8 control-label muted" style="text-align: left;font-weight:400;">
                                    <?php echo $uas->getUser()->getEmail(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">My URL</label>
                                <div class="col-lg-8 control-label muted" style="text-align: left;font-weight:400;">
                                    <?php
                                    $link = '/u/' . $uas->getUser()->getUniqueId();
                                    echo '<a href=\'' . $link . '\'>' . $link . '</a>';
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Last Login</label>
                                <div class="col-lg-8 control-label muted" style="text-align: left;font-weight:400;">
                                    <?php echo $uas->getUser()->getLastLoginTimestamp() . ' CST from ' . $uas->getUser()->getLastLoginIp(); ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div class="panel panel-default pi-panel">
                    <div class="panel-body">
                        <form class="form-horizontal" method="post" data-validate="parsley">
                            <div class="form-group">
                                <label class="col-md-3 control-label not">Old Password</label>
                                <div class="col-md-8">
                                    <input type="password" name="password-old" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label not">New Password</label>
                                <div class="col-md-8">
                                    <input type="password" name="password" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label not">Confirm New Password</label>
                                <div class="col-md-8">
                                    <input type="password" name="password-confirm" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-3">
                                    <button type="submit" name="update-password" class="btn btn-primary">Update Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php
__footer();