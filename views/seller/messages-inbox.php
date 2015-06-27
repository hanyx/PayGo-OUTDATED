<?php
$viewMessage = false;

if (count($url) == 5) {
	if ($url[3] == "view") {
		$currentMessage = new Message();

		if (!$currentMessage->read($url[4]) || $currentMessage->getFolder() != MessageFolder::INBOX || !$currentMessage->isOwner($uas->getUser()->getId())) {
			$uas->addMessage(new ErrorSuccessMessage("Message not found"));
		} else {
			$viewMessage = true;
			
			$currentMessage->setRead();
			
			$currentMessage->update();
		}
	}
}

$messages = $uas->getUser()->getMessages();

if (count($messages) == 0) {
	$uas->addMessage(new ErrorSuccessMessage("No messages found"));
}

__header();
?>
<section class="hbox stretch">
    <?php
    if (count($messages) != 0) {
        ?>
    <aside class="bg-light lter">
        <section class="vbox">
            <section class="scrollable w-f">
                <ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg">
                <?php
                foreach ($messages as $message) {
                    ?>
                    <li class="list-group-item <?php echo $viewMessage ? ($currentMessage->getId() == $message->getId() ? "active" : "") : ""; ?>">
                        <a class="clear" href="/seller/messages/inbox/view/<?php echo $message->getId(); ?>">
                            <small class="pull-right"><?php echo formatTime(strtotime($message->getDate())); ?> ago</small>
                            <strong><?php echo $message->getSender(); ?></strong> -
                            <span><?php if (!$message->getRead()) { ?><strong><?php } ?><?php echo $message->getExcerpt(); ?><?php if (!$message->getRead()) { ?></strong><?php } ?></span>
                        </a>
                    </li>
                    <?php
                }
                ?>
                </ul>
            </section>
        </section>
    </aside>
    <?php
    }

    if ($viewMessage) {
        ?>
        <aside id="email-content" class="bg-white hide col-lg-6 b-l show">
            <section class="vbox">
                <section class="scrollable">
                    <div class="text-sm padder m-t">
                        <div class="block clearfix m-b">
                            <span class="inline m-t-xs">
                                <?php echo $currentMessage->getSender(); ?>
                            </span>
                            <div class="pull-right inline">
                                <?php echo formatDate(strtotime($currentMessage->getDate())) . " (" . formatTime(strtotime($currentMessage->getDate())) . " ago)"; ?>
                            </div>
                        </div>
                        <div class="line pull-in"></div>
                        <p>
                            <?php echo $currentMessage->getMessage(); ?>
                        </p>
                    </div>
                    <div class="padder">
                        <div class="panel text-sm bg-light">
                            <div class="panel-body">
                                Click here to <a class="reply-btn" href="/seller/messages/reply/<?php echo $currentMessage->getId(); ?>">Reply</a>
                            </div>
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    <?php
    }
    ?>
</section>
<?php
__footer();