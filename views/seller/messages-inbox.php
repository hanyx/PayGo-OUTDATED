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
	} else if ($url[3] == "delete") {
		$currentMessage = new Message();

		if ($currentMessage->read($url[4]) && $currentMessage->getFolder() == MessageFolder::INBOX && $currentMessage->isOwner($uas->getUser()->getId()) && !$currentMessage->isDeleted()) {

			$currentMessage->setDeleted();

            $currentMessage->update();

			$uas->addMessage(new ErrorSuccessMessage("Message deleted"));
		}
	}
}

$messages = $uas->getUser()->getMessages();

if (count($messages) == 0) {
	$uas->addMessage(new ErrorSuccessMessage("No messages found"));
}

include_once('header.php');
?>
	<section id="content" class="content-sidebar bg-white">
		<?php
		if (count($messages) != 0) {
		?>
			<aside class="sidebar sidebar-large">
				<div class="list-group list-normal m-t-n-xmini scroll-y scrollbar" style="max-height:400px">
					<?php
					foreach ($messages as $message) {
						echo "<a href='/seller/messages/inbox/view/" . $message->getId() . "' class='list-group-item " . ($viewMessage ? ($currentMessage->getId() == $message->getId() ? "active" : "") : "") .  "'><small class='pull-right text-muted'>" . formatTime(strtotime($message->getDate())) . " ago</small>" . (!$message->getRead() ? "<b>" : "") . $message->getSender() . (!$message->getRead() ? "</b>" : "") . "<br><small>" . $message->getExcerpt() . "</small></a>";
					}
					?>
				</div>
			</aside>
		<?php
		}
		?>
		<section class="main">
			<?php
			if ($uas->hasMessage()) {
			?>
				<div class="text-small padder padder-v">
					<?php $uas->printMessages(); ?>
				</div>
			<?php
			}
			
			if ($viewMessage) {
			?>
				<div class="text-small padder">
					<div class="block clearfix" style="margin-top: 10px;">
						<a href="#" class="thumb-mini inline"><img src="/images/avatar.png" class="img-circle"></a> <?php echo $currentMessage->getSender(); ?>
						<div class="pull-right inline"><?php echo formatDate(strtotime($currentMessage->getDate())) . " (" . formatTime(strtotime($currentMessage->getDate())) . " ago)"; ?></div>
					</div>
					<p>
						<?php echo $currentMessage->getMessage(); ?>
					</p>
				</div>
				<div class="padder">
					<form class="panel text-small">
						<div class="panel-body">Click here to <a class="reply-btn" href="/seller/messages/reply/<?php echo $currentMessage->getId(); ?>">Reply</a> or <a href="/seller/messages/inbox/delete/<?php echo $currentMessage->getId(); ?>">Delete</a></div>
					</form>
				</div>
			<?php
			}
			?>
		</section>
	</section>
<?php
include_once('footer.php');