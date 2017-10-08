<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Token
 *
 * @author Ing. Erick Suárez Buendía
 */
class Token {

    var $token;

    public function __construct() {
        
    }

    public static function getToken($apiKey, $infouser) {
        $now = new DateTime("NOW");
        $token = md5($apiKey) . '.' . md5($infouser["management"]) . '.' . base64_encode($now->format('Y-m-d H:i:s'));
        return $token;
    }

    public function time($token) {
        $data = explode(".", $token);
        try {
            $time = base64_decode($data[2]);
        } catch (Exception $e) {
            $time = 0;
        }
        return $time;
    }

    public function expired($time) {
        $now = new DateTime("NOW");
        $timeExpired = new DateTime($time);
        $diff = $now->diff($timeExpired);

        $min = $diff->format("i");

        if ( $timeExpired->format("H") == $now->format("H") ) {
            if ($min < '5' || $min < 5) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function validated($token) {

        $time = $this->time($token["x-auth-token"]);
        $valid = $this->expired($time);

        return $valid;
    }
 

}
