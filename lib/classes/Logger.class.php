<?php

class Logger {

    public static function log($message) {
        $timestamp = date('Y-m-d H:i:s');

        if (is_array($message)) {
            $message = serialize($message);
        }

        $q = DB::getInstance()->prepare('INSERT INTO logs (date, message) VALUES (?, ?)');
        $q->execute(array($timestamp, $message));
    }

    public static function logAndDie($message) {
        self::log($message);

        die();
    }

}