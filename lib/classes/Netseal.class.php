<?php
class Status {

    static $SUCCESS = "\x0";
    static $FAILED = "\x1";
    static $BAD_PARAMS = "\x4";
    static $BAD_LENGTH = "\x5";
    static $BAD_FORMAT = "\x6";
    static $NULL_VALUE = "\x7";
    static $USED_VALUE = "\x8";
    static $ACCESS_DENIED = "\x9";
    static $LIMIT_REACHED = "\xA";
    static $SYSTEM_OFFLINE = "\xFF";

}

class NetSeal {

    private $response;
    private $lastError;

    private function callFunction($name, $data ,$nKEY) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://seal.nimoru.com/Remote/$name.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $data['key'] = $nKEY;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $this->response = curl_exec($ch);
        curl_close($ch);

        $length = strlen($this->response);
        if (!$this->response || $length == 0) {
            $this->lastError = Status::$FAILED;
            return false;
        } else {
            $this->lastError = $this->response[0];
            return ($this->lastError == Status::$SUCCESS);
        }
    }

    public function createCode($time, $points, $type, $track, $nKEY) {
        $data = array(
            'time' => $time,
            'points' => $points,
            'type' => $type,
            'track' => $track
        );

        if ($this->callFunction('createCode3', $data, $nKEY)) {
            return substr($this->response, 1);
        }

        return false;
    }

}