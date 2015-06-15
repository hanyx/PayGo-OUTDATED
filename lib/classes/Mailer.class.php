<?php

class Mailer {

	public function sendTemplate($template, $email, $username, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null) {
		global $config;

		switch ($template) {
			case EmailTemplate::ACTIVATE:
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
				$subject = 'Payment detail change for your account on PayIvy';

				$message =
				'Hey there ' . $username . ',

				Your payment details on PayIvy been changed

				If you did not initate this change, your account has most likely been compromised by an unauthorized party. We recommend that you immediately change your password and update your payment details.

				This request was initiated by a user with the IP: ' . $arg2 . '
				';
				break;
			case EmailTemplate::SELLERMESSAGE:
				$subject = 'New message from ' . $username . ' on PayIvy';
				
				$message = 
				'Hey there ' . $email . ',
				
				You\'ve received a message from ' . $username . ':
				
				' . $arg1 . '
				';
				
				break;
			case EmailTemplate::USERMESSAGE:
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
            case EmailTemplate::DOWNLOAD:
                $subject = 'Your product download from PayIvy.com';

                if ($arg2 != '') {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.

                    Here\'s a custom message from the seller of the item: ' . $arg2 . '

                    ';
                } else {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.';
                }

                $message .= '

                Here\'s a link to download your product: <b><a href="' . $config['url']['protocol'] . $config['url']['domain'] . '/download/' . $arg1 . '">' . $config['url']['protocol'] . $config['url']['domain'] . '/download/' . $arg1 . '</a></b>
                
                Transaction ID: ' . $arg4;

                break;
            case EmailTemplate::OUTOFSTOCK:
                $subject = 'Your recent purchase on PayIvy';

                $message =
                'Hey there,

				We received your purchase order, but the item is now out of stock. Please contact the seller of the product to receive a refund.
				';

                break;
            case EmailTemplate::SERIALS:
                $subject = 'Your purchase on PayIvy';

                if ($arg2 != '') {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.

                    Here\'s a custom message from the seller of the item: ' . $arg2 . '

                    ';
                } else {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.
                    ';
                }

                $message .= 'Here are the serials you purchased:

                <b>' . implode('</b><br><b>', $arg1) . '</b>
                
                Transaction ID: ' . $arg4;

                break;
            case EmailTemplate::NETSEALS:
                $subject = 'Your purchase on PayIvy';

                if ($arg2 != '') {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.

                    Here\'s a custom message from the seller of the item: ' . $arg2 . '

                    Here is the product you purchased:
                    ';
                } else {
                    $message =
                    'Hey there,

                    Thanks for your recent purchase on PayIvy.com.

                    Here is the product you purchased:
                    ';
                }

                $x = 0;
                foreach ($arg1 as $key) {
                    $x++;
                    if (count($arg1) > 1) {
                        $message .= '<b>Product ' . $x . '</b><br><br>';
                    }

                    $message .= '<b>Download: ' . $key[0] . '</b>
                    <b>Serial: ' . $key[1] . '</b><br><br>';
                }

                $message .= '
                
                Transaction ID: ' . $arg4;

                break;
            case EmailTemplate::SELLERSALE:
                $subject = 'New purchase of ' . $arg1;

                $message =
                'Hey there, ' . $username . ',

                We\'ve received a new purchase for your item <b>' . $arg1 . '</b>

                Here are the details:

                Amount Per Item: <b>' . $arg2 . '</b>
                Quantity: <b>' . $arg3 . '</b>
                Total Amount: <b>' . $arg4 . '</b>
                Buyer Email: <b>' . $arg5 . '</b>
                Transaction ID: <b>' . $arg6 . '</b>
                ';

                foreach ($arg7 as $question) {
                    $message .= $question[0] . ': <b>' . $question[1] . '</b><br>';
                }

                break;
            case EmailTemplate::AFFILIATEREGISTER:
                $subject = 'Affiliate Registration on PayIvy.com';

                $message =
                'Hey there,

                Thanks for signing up as an affiliate. Here is your affiliate information:

                Affiliate Url: <b>' . $arg1 . '</b>
                Password: <b>' . $arg2 . '</b>';

                break;
		}

        if ($template == EmailTemplate::DOWNLOAD || $template == EmailTemplate::OUTOFSTOCK || $template == EmailTemplate::SERIALS || $template == EmailTemplate::NETSEALS) {
            $messaged = new Message();

            $messaged->setMessage($message);
            $messaged->setRecipient($email);
            $messaged->setSender($arg3);
            $messaged->setFolder(MessageFolder::PRODUCTDELIVERY);

            $messaged->create();
        }

		$this->send($email, $message, $subject);
		return true;
	}
	
	public function send($email, $content, $subject) {
		global $config;
        $this->content = $content;
		
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


        $url = 'https://api.sendgrid.com/';

        $params = array(
            'api_user'  => $config['sendgrid']['username'],
            'api_key'   => $config['sendgrid']['password'],
            'to'        => $email,
            'subject'   => $subject,
            'html'      => $html,
            'from'      => 'noreply@payivy.com',
        );


        $request =  $url.'api/mail.send.json';


        $session = curl_init($request);
        curl_setopt ($session, CURLOPT_POST, true);
        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($session);
        curl_close($session);

        Logger::log('Mail: Email: ' . $email . ' Subject: ' . $subject . ' Message: ' . $content);

		return true;
	}
	
}