<?php
$reply = false;

if (count($url) == 4) {
	if ($url[2] == 'reply') {
		$message = new Message();

		if (!$message->read($url[3]) || $message->getFolder() != MessageFolder::INBOX || !$message->isOwner($uas->getUser()->getId())) {
			$uas->addMessage(new ErrorSuccessMessage('Message not found'));
		} else {
			$reply = true;
		}
	}
}

if (isset($_POST['send']) && isset($_POST['recipient']) && isset($_POST['message'])) {
	if (!filter_var($_POST['recipient'], FILTER_VALIDATE_EMAIL)) {
		$uas->addMessage(new ErrorSuccessMessage('Invalid Email'));
	} else {
        try {
            NoCSRF::check('csrf_token', $_POST, true, 60 * 10, false);
            $message = new Message();

            $message->setSender($uas->getUser()->getId());
            $message->setRecipient($_POST['recipient']);
            $message->setMessage(stripTags($_POST['message']));

            $message->send();

            $uas->addMessage(new ErrorSuccessMessage('Message sent!', false));
        } catch (Exception $e) {

        }
	}
}

__header('Compose Message');
?>
    <div class="wrapper">
        <?php
        if ($uas->hasMessage()) {
        ?>
            <div class='text-small padder padder-v'>
                <?php $uas->printMessages(); ?>
            </div>
        <?php
        } else {
        ?>
            <form action='/seller/messages/compose' method='post' style='margin-bottom: 0;'>
                <input type="hidden" name="csrf_token" value="<?php echo NoCSRF::generate('csrf_token'); ?>"/>
                <div class='form-group'>
                    <div class='input-group'>
                        <span class='input-group-addon'>TO:</span>
                        <input name='recipient' type='email' class='form-control' placeholder='Email To' value='<?php echo ($reply) ? $message->getSender() : ''; ?>'>
                    </div>
                </div>
                <div class='form-group'>
                    <textarea name='message' id='message-body' class='form-control wysi' placeholder='Message' style='height: 120px;'>
                        <?php
                            if ($reply) {
                                echo '<br><br>';
                                echo '<i>';
                                echo 'On ' . $message->getDate() . ' ' . $message->getSender() . ' wrote: <br>';
                                echo htmlspecialchars($message->getMessage());
                                echo '</i>';
                            }
                        ?>
                    </textarea>
                </div>
                <button type='submit' name='send' class='btn btn-primary pull-left'><i class='fa fa-envelope'></i> Send Message</button>
            </form>
        <?php
        }
        ?>
    </div>
    </div>
<?php
__footer();