<?php

namespace paybook;

class Provider extends Paybook
{
    public function __construct($provider_array = null)
    {
        $this->id = array_key_exists('id', $provider_array) ? $provider_array['id'] : '';
        $this->name = array_key_exists('name', $provider_array) ? $provider_array['name'] : null;
    }//End of __construct

    public static function get($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Provider->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $providers = self::call($endpoint = '/invoicing/mx/providers', $method = 'get', $params = $params);
        $provider_instances = [];
        foreach ($providers as $provider_array) {
            $provider_instance = new self($provider_array);
            array_push($provider_instances, $provider_instance);
        }//End of foreach
        return $provider_instances;
    }//End of get
}//End of Provider class
