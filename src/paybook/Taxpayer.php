<?php

namespace paybook;

class Taxpayer extends Paybook
{
    public function __construct($session = null, $id_user = null, $taxpayer = null, $cer = null, $key = null, $password = null, $taxpayer_array = null)
    {
        self::log('');
        self::log('Taxpayer->__construct');

        if ($taxpayer && $cer && $key && $password) {
            if ($id_user != null) {
                $data = [
                    'api_key' => self::$api_key,
                    'id_user' => $id_user,
                ];//End of $data
            } else {
                $data = [
                    'token' => $session->token,
                ];//End of $data
            }//End of if
            $data['taxpayer'] = $taxpayer;
            $data['cer'] = $cer;
            $data['key'] = $key;
            $data['password'] = $password;
            $taxpayer_array = self::call($endpoint = '/invoicing/mx/taxpayers', $method = 'post', $data = $data);
        }
        $this->taxpayer = array_key_exists('taxpayer', $taxpayer_array) ? $taxpayer_array['taxpayer'] : '';
        $this->cerValidFrom = array_key_exists('cerValidFrom', $taxpayer_array) ? $taxpayer_array['cerValidFrom'] : null;
        $this->cerValidTo = array_key_exists('cerValidTo', $taxpayer_array) ? $taxpayer_array['cerValidTo'] : null;
    }//End of __construct

    public static function get($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Taxpayer->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $taxpayers = self::call($endpoint = '/invoicing/mx/taxpayers', $method = 'get', $params = $params);
        $taxpayer_instances = [];
        foreach ($taxpayers as $taxpayer_array) {
            $taxpayer_instance = new self(null, null, null, null, null, null, $taxpayer_array);
            array_push($taxpayer_instances, $taxpayer_instance);
        }//End of foreach
        return $taxpayer_instances;
    }//End of get
}//End of Taxpayer class
