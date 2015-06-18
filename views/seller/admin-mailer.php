<?php
//TODO: Move to dedicated admin panel
if (isset($_POST['custom-list-l']) && isset($_POST['subject']) && isset($_POST['message'])) {
    $emails = array();

    if (isset($_POST['sellers']) && $_POST['sellers'] == 1) {
        $q = DB::getInstance()->prepare('SELECT email FROM users');
        $q->execute();
        $q = $q->fetchAll();

        foreach ($q as $e) {
            $emails[] = $e['email'];
        }
    }

    if (isset($_POST['buyers']) && $_POST['buyers'] == 1) {
        $q = DB::getInstance()->prepare('SELECT email FROM orders WHERE completed = 1');
        $q->execute();
        $q = $q->fetchAll();

        foreach ($q as $e) {
            $emails[] = $e['email'];
        }
    }

    if (isset($_POST['custom-list']) && $_POST['custom-list'] == 1) {
        $x = explode(',', $_POST['custom-list-l']);

        foreach ($x as $e) {
            $emails[] = $e;
        }
    }

    $mailer = new Mailer();

    $mailer->send($emails, stripTags($_POST['message']), htmlspecialchars($_POST['subject'], ENT_QUOTES));

    $uas->addMessage(new ErrorSuccessMessage('Sent!', false));
}

__header('Mailer');
?>
    <section class='wrapper'>
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <div class='row'>
            <div class="col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <form class="bs-example form-horizontal" method="post">
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Recipients</label>
                                <div class="col-lg-10">
                                    <label class="switch">
                                        <input type='checkbox' name='sellers' value='1'>
                                        <span></span>
                                        Sellers
                                    </label>
                                    <br>
                                    <label class="switch">
                                        <input type='checkbox' name='buyers' value='1'>
                                        <span></span>
                                        Buyers
                                    </label>
                                    <br>
                                    <label class="switch">
                                        <input type='checkbox' name='custom-list' value='1'>
                                        <span></span>
                                        Custom List
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Custom List</label>
                                <div class="col-lg-10">
                                    <input name='custom-list-l' class='form-control' placeholder='Emails seperated by commas'>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Subject</label>
                                <div class="col-lg-10">
                                    <input name='subject' class='form-control'>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Message</label>
                                <div class="col-lg-10">
                                    <textarea name='message' type='text' class='form-control wysi' id='description' style='height: 160px;' ></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-sm btn-primary" type="submit">Send</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
<?php
__footer();