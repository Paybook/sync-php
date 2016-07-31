<?php

namespace paybook;

class Session extends Paybook
{
    public function __construct($user = null, $token = null)
    {
        $this->user = $user;
        if ($token != null) {
            $this->token = $token;
            $this->iv = null;
            $this->key = null;
        } else {
            self::log('');
            self::log('Session->__construct');
            $data = [
                'api_key' => self::$api_key,
                'id_user' => $this->user->id_user,
            ];//End of $data
            $session_array = self::call($endpoint = 'sessions', $method = 'post', $data = $data);
            $this->token = $session_array['token'];
            $this->iv = $session_array['iv'];
            $this->key = $session_array['key'];
        }//End of if
    }//End of __construct

    public function verify()
    {
        self::log('');
        self::log('Session->verify');
        $session_array = self::call($endpoint = 'sessions/'.$this->token.'/verify', $method = 'get');

        return true;
    }//End of verify

    public static function delete($token)
    {
        self::log('');
        self::log('Session->delete');
        $session_array = self::call($endpoint = 'sessions/'.$token, $method = 'delete');

        return true;
    }//End of delete

    public static function set_token($token)
    {
        $this->token = $token;
    }//End of set_token

    public static function get_array()
    {
        return [
            'iv' => $this->iv,
            'key' => $this->key,
            'token' => $this->token,
        ];//End of return
        $this->token = $token;
    }//End of get_array
}//End of Session class
