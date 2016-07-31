<?php

namespace paybook;

class Account extends Paybook
{
    public function __construct($account_array)
    {
        $this->id_account = array_key_exists('id_account', $account_array) ? $account_array['id_account'] : '';
        $this->id_user = array_key_exists('id_user', $account_array) ? $account_array['id_user'] : '';
        $this->id_external = array_key_exists('id_external', $account_array) ? $account_array['id_external'] : '';
        $this->id_credential = array_key_exists('id_credential', $account_array) ? $account_array['id_credential'] : '';
        $this->id_site = array_key_exists('id_site', $account_array) ? $account_array['id_site'] : '';
        $this->id_site_organization = array_key_exists('id_site_organization', $account_array) ? $account_array['id_site_organization'] : '';
        $this->name = array_key_exists('name', $account_array) ? $account_array['name'] : '';
        $this->number = array_key_exists('number', $account_array) ? $account_array['number'] : '';
        $this->balance = array_key_exists('balance', $account_array) ? $account_array['balance'] : 0;
        $this->site = array_key_exists('site', $account_array) ? $account_array['site'] : '';
        $this->dt_refresh = array_key_exists('dt_refresh', $account_array) ? $account_array['dt_refresh'] : '';
    }//End of __construct

    public static function get($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Account->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $account_arrays = self::call($endpoint = 'accounts', $method = 'get', $params = $params);
        $accounts = [];
        foreach ($account_arrays as $index => $account_array) {
            $account = new self($account_array);
            array_push($accounts, $account);
        }//End of foreach
        return $accounts;
    }//End of get

    public function get_array()
    {
        return [
            'id_account' => $this->id_account,
            'id_user' => $this->id_user,
            'id_external' => $this->id_external,
            'id_credential' => $this->id_credential,
            'id_site' => $this->id_site,
            'id_site_organization' => $this->id_site_organization,
            'name' => $this->name,
            'number' => $this->number,
            'balance' => $this->balance,
            'site' => $this->site,
            'dt_refresh' => $this->dt_refresh,
        ];//End of return 
    }//End of get_array
}//End of Account class
