<?php

namespace paybook;

class Catalogues extends Paybook
{
    public static function get_account_types($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Catalogues->get_account_types');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $account_type_arrays = self::call($endpoint = 'catalogues/account_types', $method = 'get', $params = $params);
        $account_types = [];
        foreach ($account_type_arrays as $index => $account_type_array) {
            $account_type = new Account_type($account_type_array);
            array_push($account_types, $account_type);
        }//End of foreach
        return $account_types;
    }//End of get_account_types

    public static function get_attachment_types($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Catalogues->get_attachment_types');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $attachment_type_arrays = self::call($endpoint = 'catalogues/attachment_types', $method = 'get', $params = $params);
        $attachment_types = [];
        foreach ($attachment_type_arrays as $index => $attachment_type_array) {
            $attachment_type = new Attachment_type($attachment_type_array);
            array_push($attachment_types, $attachment_type);
        }//End of foreach
        return $attachment_types;
    }//End of get_attachment_types

    public static function get_countries($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Catalogues->get_countries');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $country_arrays = self::call($endpoint = 'catalogues/countries', $method = 'get', $params = $params);
        $countries = [];
        foreach ($country_arrays as $index => $country_array) {
            $country = new Country($country_array);
            array_push($countries, $country);
        }//End of foreach
        return $countries;
    }//End of get_countries

    public static function get_sites($session = null, $id_user = null, $options = [], $is_test = false)
    {
        self::log('');
        self::log('Catalogues->get_sites');
        if ($options == null) {
            // When is not given as optional param could be equal to null
            $options = [];
        }//End of if
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        if ($is_test == true) {
            $params['is_test'] = $is_test;
        }//End of if
        $site_arrays = self::call($endpoint = 'catalogues/sites', $method = 'get', $params = $params);
        $sites = [];
        foreach ($site_arrays as $index => $site_array) {
            $site = new Site($site_array);
            array_push($sites, $site);
        }//End of foreach
        return $sites;
    }//End of get_sites

    public static function get_site_organizations($session = null, $id_user = null, $options = [], $is_test = false)
    {
        self::log('');
        self::log('Catalogues->get_site_organizations');
        if ($options == null) {
            // When is not given as optional param could be equal to null
            $options = [];
        }//End of if
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        if ($is_test == true) {
            $params['is_test'] = $is_test;
        }//End of if
        $site_organization_arrays = self::call($endpoint = 'catalogues/site_organizations', $method = 'get', $params = $params);
        $site_organizations = [];
        foreach ($site_organization_arrays as $index => $site_organization_array) {
            $site_organization = new Site_organization($site_organization_array);
            array_push($site_organizations, $site_organization);
        }//End of foreach
        return $site_organizations;
    }//End of get_site_organizations
}//End of Catalogues class

class Account_type
{
    public function __construct($account_type_array)
    {
        $this->id_account_type = array_key_exists('id_account_type', $account_type_array) ? $account_type_array['id_account_type'] : '';
        $this->name = array_key_exists('name', $account_type_array) ? $account_type_array['name'] : '';
    }//End of __construct
}//End of Account_type

class Attachment_type
{
    public function __construct($attachment_type_array)
    {
        $this->id_attachment_type = array_key_exists('id_attachment_type', $attachment_type_array) ? $attachment_type_array['id_attachment_type'] : '';
        $this->name = array_key_exists('name', $attachment_type_array) ? $attachment_type_array['name'] : '';
    }//End of __construct
}//End of Attachment_type

class Country
{
    public function __construct($country_array)
    {
        $this->id_country = array_key_exists('id_country', $country_array) ? $country_array['id_country'] : '';
        $this->name = array_key_exists('name', $country_array) ? $country_array['name'] : '';
        $this->code = array_key_exists('code', $country_array) ? $country_array['code'] : '';
    }//End of __construct
}//End of Country

class Site
{
    public function __construct($site_array)
    {
        $this->id_site = array_key_exists('id_site', $site_array) ? $site_array['id_site'] : '';
        $this->id_site_organization = array_key_exists('id_site_organization', $site_array) ? $site_array['id_site_organization'] : '';
        $this->id_site_organization_type = array_key_exists('id_site_organization_type', $site_array) ? $site_array['id_site_organization_type'] : '';
        $this->name = array_key_exists('name', $site_array) ? $site_array['name'] : '';
        $credentials_structures = [];
        foreach ($site_array['credentials'] as $index => $credential_structure_array) {
            $credentials_structure = new Credentials_structure($credential_structure_array);
            array_push($credentials_structures, $credentials_structure);
        }//End of foreach
        $this->credentials = array_key_exists('credentials', $site_array) ? $site_array['credentials'] : '';
        $this->site_array = $site_array;
    }//End of __construct

    public function get_array()
    {
        return $this->site_array;
    }//End of get_array
}//End of Site

class Credentials_structure
{
    public function __construct($credential_structure_array)
    {
        $this->name = array_key_exists('name', $credential_structure_array) ? $credential_structure_array['name'] : '';
        $this->type = array_key_exists('type', $credential_structure_array) ? $credential_structure_array['type'] : '';
        $this->label = array_key_exists('label', $credential_structure_array) ? $credential_structure_array['label'] : '';
        $this->required = array_key_exists('required', $credential_structure_array) ? $credential_structure_array['required'] : '';
        $this->username = array_key_exists('username', $credential_structure_array) ? $credential_structure_array['username'] : '';
        $this->validation = null;
    }//End of __construct
}//End of Credentials_structure

class Site_organization
{
    public function __construct($site_organization_array)
    {
        $this->id_site_organization = array_key_exists('id_site_organization', $site_organization_array) ? $site_organization_array['id_site_organization'] : '';
        $this->id_site_organization_type = array_key_exists('name', $site_organization_array) ? $site_organization_array['name'] : '';
        $this->name = array_key_exists('id_site_organization_type', $site_organization_array) ? $site_organization_array['id_site_organization_type'] : '';
        $this->id_country = array_key_exists('id_country', $site_organization_array) ? $site_organization_array['id_country'] : '';
        $this->name = array_key_exists('name', $site_organization_array) ? $site_organization_array['name'] : '';
        $this->avatar = array_key_exists('avatar', $site_organization_array) ? $site_organization_array['avatar'] : '';
        $this->small_cover = array_key_exists('small_cover', $site_organization_array) ? $site_organization_array['small_cover'] : '';
        $this->cover = array_key_exists('cover', $site_organization_array) ? $site_organization_array['cover'] : '';
        $this->site_organization_array = $site_organization_array;
    }//End of __construct

    public function get_array()
    {
        return $this->site_organization_array;
    }//End of get_array
}//End of Site_organization
