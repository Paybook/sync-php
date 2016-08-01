<?php

namespace paybook;

class User extends Paybook
{
    public function __construct($name = null, $id_user = null, $user_array = null)
    {
        // self::log('');
        // self::log('User->__construct');
        if ($user_array === null) {
            if ($id_user == null && $name != null) {
                self::log('Creating new user ... ');
                $data = [
                    'api_key' => self::$api_key,
                    'name' => $name,
                ];//End of $data
                $user_array = self::call($endpoint = 'users', $method = 'post', $data = $data);
            } elseif ($id_user != null) {
                self::log('Retrieving existing user ... ');
                $existing_users = self::get();
                for ($i = 0; $i < count($existing_users); ++$i) {
                    $id_existing_user = $existing_users[$i]->id_user;
                    if ($id_existing_user == $id_user) {
                        $user_array = $existing_users[$i]->get_array();
                    }//End of if
                }//End of for
            }//End of if
        }//End of if
        $this->name = $user_array['name'];
        $this->id_user = $user_array['id_user'];
        $this->id_external = $user_array['id_external'];
        $this->dt_create = $user_array['dt_create'];
        $this->dt_modify = $user_array['dt_modify'];
    }//End of __construct

    public static function delete($id_user)
    {
        self::log('');
        self::log('User->delete');
        $params = [
            'api_key' => self::$api_key,
        ];//End of $params
        $delete_response = self::call($endpoint = 'users/'.$id_user, $method = 'delete', $params = $params);

        return true;
    }//End of delete

    public static function get($options = [])
    {
        self::log('');
        self::log('User->get');
        $users = [];
        $params = [
            'api_key' => self::$api_key,
        ];//End of $params
        $user_arrays = self::call($endpcoint = 'users', $method = 'get', $params = $params);
        for ($i = 0; $i < count($user_arrays); ++$i) {
            $user_array = $user_arrays[$i];
            $user = new self($name = null, $id_user = null, $user_array = $user_array);
            array_push($users, $user);
        }//End of for
        return $users;
    }//End of get

    public function get_array()
    {
        return [
            'name' => $this->name,
            'id_user' => $this->id_user,
            'id_external' => $this->id_external,
            'dt_create' => $this->dt_create,
            'dt_modify' => $this->dt_modify,
        ];//End of return
    }//End of get_array
}//End of User class
