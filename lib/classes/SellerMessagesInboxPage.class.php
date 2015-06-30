<?php
class SellerMessagesInboxPage extends Page {

	public function getAlerts(User $user) {
		$inbox = $user->getMessages();

		$unread = 0;

		foreach ($inbox as $message) {
			if (!$message->getRead()) {
				$unread ++;
			}
		}
		
		return $unread;
	}

}