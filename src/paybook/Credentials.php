<?php

namespace paybook;

class Credentials extends Paybook
{
    public function __construct($session = null, $id_user = null, $id_site = null, $credentials = null, $credentials_array = null)
    {
        self::log('');
        self::log('Credentials->__construct');
        $this->status = null;
        if (is_null($session) && is_null($id_user) && is_null($id_site) && is_null($credentials) && is_null($credentials_array)) {
            return;
        }
        $this->id_site = null;
        $this->twofa = null;
        $this->twofa_config = null;
        if ($credentials_array == null) {
            if ($id_user != null) {
                $data = [
                    'api_key' => self::$api_key,
                    'id_user' => self::$id_user,
                ];//End of $data
            } else {
                $data = [
                    'token' => $session->token,
                ];//End of $data
            }//End of if
            $data['id_site'] = $id_site;
            $data['credentials'] = $credentials;
            $credentials_array = self::call($endpoint = 'credentials', $method = 'post', $data = $data);
        }//End of if
        if ($id_site != null) {
            $this->id_site = $id_site;
        } elseif (array_key_exists('id_site', $credentials_array)) {
            $this->id_site = $credentials_array['id_site'];
        }//End of if
        $this->id_credential = $credentials_array['id_credential'];
        $this->username = $credentials_array['username'];
        $this->dt_refresh = array_key_exists('dt_refresh', $credentials_array) ? $credentials_array['dt_refresh'] : null;
        $this->id_site_organization = array_key_exists('id_site_organization', $credentials_array) ? $credentials_array['id_site_organization'] : null;
        $this->id_site_organization_type = array_key_exists('id_site_organization_type', $credentials_array) ? $credentials_array['id_site_organization_type'] : null;
        $this->ws = array_key_exists('ws', $credentials_array) ? $credentials_array['ws'] : null;
        $this->status = array_key_exists('status', $credentials_array) ? $credentials_array['status'] : null;
        $this->twofa = array_key_exists('twofa', $credentials_array) ? $credentials_array['twofa'] : null;
        $this->keywords = array_key_exists('keywords', $credentials_array) ? $credentials_array['keywords'] : null;
    }//End of __construct

    public static function delete($session = null, $id_user = null, $id_credential = null)
    {
        self::log('');
        self::log('Credentials->delete');
        if ($id_user != null) {
            $data = [
                'api_key' => self::$api_key,
                'id_user' => self::$id_user,
            ];//End of $data
        } else {
            $data = [
                'token' => $session->token,
            ];//End of $data
        }//End of if
        $delete_response = self::call($endpoint = 'credentials/'.$id_credential, $method = 'delete', $data = $data);

        return true;
    }//End of delete

    public static function get($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Credentials->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $credentials_list = self::call($endpoint = 'credentials', $method = 'get', $params = $params);
        $credentials_objects_list = [];
        foreach ($credentials_list as $index => $credentials_array) {
            $credentials = new self($session = null, $id_user = null, $id_site = null, $credentials = null, $credentials_array = $credentials_array);
            array_push($credentials_objects_list, $credentials);
        }//End of foreach
        return $credentials_objects_list;
    }//End of get

    public function get_status($session = null, $id_user = null)
    {
        self::log('');
        self::log('Credentials->get_status');
        $params = [];
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $status = self::call($endpoint = null, $method = 'get', $data = $params, $params = $params, $headers = '', $url = $this->status);
        foreach ($status as $index => $each_status) {
            $code = $each_status['code'];
            if ($code == 410) {
                $this->twofa = $each_status['address'];
                $this->twofa_config = $each_status['twofa'][0];
            }//End of if
        }//End of foreach
        return $status;
    }//End of get_status

    public function set_twofa($session = null, $id_user = null, $twofa_value = null)
    {
        self::log('');
        self::log('Credentials->set_twofa');
        $params = [];
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $params['twofa'] = [];
        $params['twofa'][$this->twofa_config['name']] = $twofa_value;
        self::call($endpoint = null, $method = 'post', $data = $params, $params = $params, $headers = '', $url = $this->twofa);

        return true;
    }//End of set_twofa

    public function sync($session = null, $id_user = null, $id_credential = null)
    {
        self::log('');
        self::log('Credentials->sync');
        if ($id_user != null) {
            $data = [
                'api_key' => self::$api_key,
                'id_user' => self::$id_user,
            ];//End of $data
        } else {
            $data = [
                'token' => $session->token,
            ];//End of $data
        }//End of if
        $credentials_array = self::call($endpoint = 'credentials/'.$id_credential.'/sync', $method = 'put', $data = $data);
        $this->id_site = null;
        $this->twofa = null;
        $this->twofa_config = null;
        $this->id_credential = $credentials_array['id_credential'];
        $this->username = $credentials_array['username'];
        $this->dt_refresh = array_key_exists('dt_refresh', $credentials_array) ? $credentials_array['dt_refresh'] : null;
        $this->id_site_organization = array_key_exists('id_site_organization', $credentials_array) ? $credentials_array['id_site_organization'] : null;
        $this->id_site_organization_type = array_key_exists('id_site_organization_type', $credentials_array) ? $credentials_array['id_site_organization_type'] : null;
        $this->ws = array_key_exists('ws', $credentials_array) ? $credentials_array['ws'] : null;
        $this->status = array_key_exists('status', $credentials_array) ? $credentials_array['status'] : null;
        $this->twofa = array_key_exists('twofa', $credentials_array) ? $credentials_array['twofa'] : null;
        $this->keywords = array_key_exists('keywords', $credentials_array) ? $credentials_array['keywords'] : null;
    }//End of sync

    public function get_array()
    {
        return [
            'id_site' => $this->id_site,
            'id_site_organization' => $this->id_site_organization,
            'dt_refresh' => $this->dt_refresh,
            'id_site_organization_type' => $this->id_site_organization_type,
            'id_credential' => $this->id_credential,
            'status' => $this->status,
            'twofa' => $this->twofa,
            'ws' => $this->ws,
            'username' => $this->username,
        ];//End of return
    }//End of get_array

    public function wait_for_status($session, $status_code)
    {
        $wait = false;
        $got_status = null;
        $try = 0;
        while (!$wait) {
            $status = $this->get_status($session);
            foreach ($status as $index => $each_status) {
                $code = $each_status['code'];
                if ($code == $status_code) {
                    $wait = true;
                    $got_status = $each_status;
                }//End of for
            }//End of foreach
            sleep(3);
            ++$try;
            // print_r($try.PHP_EOL);
            if ($try == 10) {
                break;
            }//End of if
        }//End of while 
        return $got_status;
    }//End of wait_for_status
}//End of Credentials class
