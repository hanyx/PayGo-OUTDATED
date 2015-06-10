<?php
require_once('lib/functions.php');

$x = DB::getInstance()->prepare('SELECT id FROM users WHERE id >= 12035');
$x->execute();

$x = $x->fetchAll();

foreach ($x as $y) {
    $user = new User();

    if ($user->read($y['id']) && !$user->getActive()) {
        $tfr = new TwoFactorRequest();

        $x = DB::getInstance()->prepare('SELECT token FROM 2fa WHERE user_id = ? AND used = 0 AND action = ?');
        $x->execute(array($user->getId(), TwoFactorRequestAction::ACTIVATE));

        $x = $x->fetchAll();

        if (count($x) == 1) {

            $mailer = new Mailer();

            $mailer->sendTemplate(EmailTemplate::ACTIVATE, $user->getEmail(), $user->getUsername(), $x[0]['token']);
        }
    }
}