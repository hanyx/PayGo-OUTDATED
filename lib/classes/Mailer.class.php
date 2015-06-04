<?php

class Mailer {
	
	private $mandrill;
	
	public function __construct() {
		global $config;
		
		try {
			$this->mandrill = new Mandrill($config['mandrill']['key']);
		} catch (Mandrill_Error $e) {
			
		}
	}
	
	public function sendTemplate($template, $email, $username, $arg1 = null, $arg2 = null, $arg3 = null) {
		global $config;

		switch ($template) {
			case EmailTemplate::ACTIVATE:
				if ($arg1 == null) {
					return false;
				}

				$subject = 'Activate your account on PayIvy';
				
				$message = 
				'Hey there ' . $username . ',
				
				Thanks for signing up on PayIvy.com
				
				To get started, you will need to activate your account. Without activation you won\'t be able to use your account.
				
				To activate your account, simply click the link below:
				
				<a href=\'' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/login/activate/' . $arg1 . '\'>' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/login/activate/' . $arg1 . '</a>
				
				If the URL above doesn\'t work, copy and paste it into your browser.
				';
				break;
			case EmailTemplate::RESET:
				if ($arg1 == null || $arg2 == null) {
					return false;
				}
				
				$subject = 'Password reset for your account on PayIvy';
				
				$message = 
				'Hey there ' . $username . ',
				
				We\'ve received a request to reset the password to your account on PayIvy
				
				To continue the reset process, simply click the link below:
				
				<a href=\'' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/reset/' . $arg1 . '\'>' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/reset/' . $arg1 . '</a>
				
				If you did not request to have your password reset you can safely ignore this email. Your account is safe and can\'t be compromised.
				
				If the URL above doesn\'t work, copy and paste it into your browser.
				
				This request was initiated by a user with the IP: ' . $arg2 . '
				';
				break;
			case EmailTemplate::UPDATEPASSWORD:
				if ($arg1 == null || $arg2 == null) {
					return false;
				}
				
				$subject = 'Password update for your account on PayIvy';
				
				$message = 
				'Hey there ' . $username . ',
				
				We\'ve received a request to update the password to your account on PayIvy
				
				To update your password, simply click the link below:
				
				<a href=\'' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/login/update/' . $arg1 . '\'>' . $config['url']['protocol'] . $config['url']['domain'] . '/seller/login/update/' . $arg1 . '</a>

				If you did not update your password, your account has most likely been compromised by an unauthorized party. We recommend that you immediately change your password.
				
				If the URL above doesn\'t work, copy and paste it into your browser.
				
				This request was initiated by a user with the IP: ' . $arg2;
				break;
			case EmailTemplate::UPDATEPAYMENTDETAILS:
				if ($arg1 == null || $arg2 == null) {
					return false;
				}

				$subject = 'Payment detail change for your account on PayIvy';

				$message =
				'Hey there ' . $username . ',

				Your payment details on PayIvy been changed

				If you did not initate this change, your account has most likely been compromised by an unauthorized party. We recommend that you immediately change your password and update your payment details.

				This request was initiated by a user with the IP: ' . $arg2 . '
				';
				break;
			case EmailTemplate::SELLERMESSAGE:
				if ($arg1 == null) {
					return false;
				}
				
				$subject = 'New message from ' . $username . ' on PayIvy';
				
				$message = 
				'Hey there ' . $email . ',
				
				You\'ve received a message from ' . $username . ':
				
				' . $arg1 . '
				';
				
				break;
			case EmailTemplate::USERMESSAGE:
				if ($arg1 == null || $arg2 == null) {
					return false;
				}
				
				$subject = 'New message from a user on PayIvy';
				
				$message = 
				'Hey there ' . $username . ',
				
				You\'ve received a message from ' . $arg1 . ':
				
				' . $arg2 . '
				';
				
				break;
			case EmailTemplate::AFFILIATEPAID:
				if ($arg1 == null || $arg2 == null || $arg3 == null) {
					return false;
				}
				
				$subject = 'Your affiliate account has been marked as paid';
				
				$message = 
				'Hey there ' . $email . ',
				
				Your affiliate account for the product ' . $arg1 . ' has been marked as paid by ' . $username . '
				
				Number of Orders: ' . $arg2 . '
				Amount Paid: $' . $arg3 . '
				
				Please note that this does not mean that the seller has sent any payment. This message simply serves as an automated notice. It is your responsibility to make sure you receive payments.
				';
				
				break;
		}

		$this->send($email, $message, $subject);
		return true;
	}
	
	public function send($email, $content, $subject) {
		global $config;
		
		$content = str_replace(array("\r\n","\r","\n"), '<br>', $content);

		$html = '<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.0 Transitional//EN\' \'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\'>
		<html>
		<head>
		  <meta http-equiv=\'Content-Type\' content=\'text/html; charset=UTF-8\' />
		  <title>PayIvy</title>
		</head>
		<body leftmargin=\'0\' marginwidth=\'0\' topmargin=\'0\' marginheight=\'0\' offset=\'0\' style=\'-webkit-text-size-adjust: none;margin: 0;padding: 0;background: #eeeeee;width: 100% !important;\' bgcolor=\'#eee\'>
		  <center>
			<table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' height=\'100%\' width=\'100%\' id=\'backgroundTable\' style=\'font-family: \' . \'\'Helvetica Neue\'\' . \', Helvetica, Arial, sans-serif; margin: 0;padding: 0;height: 100% !important;width: 100% !important; background: #eeeeee;\'>
			  <tr>
				<td align=\'center\' valign=\'top\'>
				  <table border=\'0\' cellpadding=\'40\' cellspacing=\'0\' width=\'480\' id=\'contentWrapper\'>
					<tr>
					  <td>
						<table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' width=\'480\' id=\'templateContainer\' style=\'background-color: #FFFFFF;\'>
						  <tr>
							<td>
							  <table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' width=\'480\'>
								<tr>
								  <td align=\'center\' valign=\'top\'>
									<table border=\'0\' cellpadding=\'24\' cellspacing=\'0\' height=\'50\' style=\'border-bottom:1px solid #eee; padding:0 16px\' width=\'480\' id=\'templateBody\'>
									  <tr>
										<td valign=\'top\' style=\'background-color: #FFFFFF;\'>
										  <a href=\'' . $config['url']['protocol'] . $config['url']['domain'] . '\'><img alt=\'PayIvy\' height=\'40\' src=\'' . $config['url']['protocol'] . $config['url']['domain'] . '/images/logo-websize.png\' /></a>
										</td>
										<td style=\'text-align: right;\'></td>
									  </tr>
									</table>
								  </td>
								</tr>
								<tr>
								  <td align=\'center\' valign=\'top\'>
									<table border=\'0\' cellpadding=\'24\' cellspacing=\'0\' width=\'480\' id=\'templateBody\' style=\'padding:0 16px 10px\'>
									  <tr>
										<td valign=\'top\' style=\'background-color: #FFFFFF;\'>
										  <font style=\'font-size:16px; color:#444; font-family: \' . \'\'Helvetica Neue\'\' . \', Helvetica, Arial, sans-serif; line-height:1.35;\'>
										  ' . $content . '
										  <br>
										  <p>If you have any questions, you can contact us at <a href=\'' . $config['url']['protocol'] . 'support.' . $config['url']['domain'] . '\'>support.' . $config['url']['domain'] . '</a></p>
										  <p>Thanks,<br>PayIvy Team</p>
										  </font>
										</td>
									  </tr>
									</table>
								  </td>
								</tr>
							  </table>
							</td>
						  </tr>
						</table>
					  </td>
					</tr>
				  </table>
				</td>
			  </tr>
			</table>
		  </center>
		</body>
		</html>';

		$message = array(
			'html' => $html,
			'subject' => $subject,
			'from_email' => 'noreply@payivy.com',
			'from_name' => 'PayIvy',
			'to' => array(
				array(
					'email' => $email,
					'type' => 'to'
				)
			)
		);
		
		try {
			$send = $this->mandrill->messages->send($message);
		} catch (Mandrill_Error $e) {
			return false;
		}

		if ($send === false) {
			return false;
		}
		
		return true;
	}
	
}