<?php

namespace paybook;

use Exception;

class Paybook
{
    public static $api_key = null;
    public static $log = false;
    public static $env_url = false;
    const INDENT = '   ';
    const PAYBOOK_URL = 'https://sync.paybook.com/v1/';

    public static function init($api_key_value, $test_init = false, $env_url = null)
    {
        if ($api_key_value === true || $api_key_value === false) {
            global $TESTING_CONFIG;
            if ($api_key_value === false) {
                self::$api_key = 'this_is_an_incorrect_api_key';
            } else {
                self::$api_key = $TESTING_CONFIG['api_key'];
            }//End of if
            self::$log = $TESTING_CONFIG['log_calls'];
            self::$env_url = $TESTING_CONFIG['env_url'];//For debugging in other environment 
            if ($test_init === true) {
                echo PHP_EOL.PHP_EOL.PHP_EOL;
                echo '------------------------------------------------------------------'.PHP_EOL.PHP_EOL;
                echo self::INDENT.'TESTING SYNC LIBRARY CONFIGURATION'.PHP_EOL.PHP_EOL;
                echo self::INDENT.self::INDENT.'-> Environment:        '.strval(self::$env_url).PHP_EOL;
                echo self::INDENT.self::INDENT.'-> API Key:            '.strval(self::$api_key).PHP_EOL.PHP_EOL;
                echo '------------------------------------------------------------------'.PHP_EOL.PHP_EOL;
            }//End of if
        } else {
            self::$api_key = $api_key_value;
            if (!is_null($env_url)) {
                self::$env_url = $env_url;
            }//End of if
        }//End of if
    }//End of init

    public static function get_env()
    {
        if (self::$env_url == false) {
            return self::PAYBOOK_URL;
        } else {
            return self::$env_url;
        }//End of if 
    }//End of init

    public static function call($endpoint = null, $method = null, $data = null, $params = null, $headers = null, $url = false, $test = false)
    {
        if ($url == null) {
            if (self::$env_url == false) {
                $url = self::PAYBOOK_URL.$endpoint;
            } else {
                $url = self::$env_url.$endpoint;
            }//End of if
        }//End of if
        $method = strtoupper($method);
        self::log('');
        // self::log(self::INDENT.'API Key:                    '.strval(self::$api_key));
        self::log(self::INDENT.'Endpoint:                   '.strval($url));
        self::log(self::INDENT.'Overwritten HTTP Method:    '.strval($method));
        // self::log(self::INDENT.'Data:           '.strval($data));
        // self::log(self::INDENT.'Params:         '.strval($params));
        // self::log(self::INDENT.'Headers:        '.strval($headers));
        if (!is_null($params)) {
            $data = $params;//Unify data (queryParams) and params (bodyParams)
        }//End of if

        /*
        Authorization Scheme
        */
        $authorization_header = '';
        if (is_null($data)) {
            $data = [];
        }//End of if

        // API KEY
        if (array_key_exists('api_key', $data) && !array_key_exists('id_user', $data)) {
            $authorization_header = 'API_KEY api_key='.$data['api_key'];
            unset($data['api_key']);
        // API KEY + ID USER
        } elseif (array_key_exists('api_key', $data) && array_key_exists('id_user', $data)) {
            $authorization_header = 'API_KEY api_key='.$data['api_key'].', id_user='.$data['id_user'];
            unset($data['api_key']);
            unset($data['id_user']);
        // TOKEN
        } elseif (array_key_exists('token', $data)) {
            $authorization_header = 'TOKEN token='.$data['token'];
            unset($data['token']);
        }//End of if

        $dataString = json_encode($data);

        self::log(self::INDENT.'Auth:                       '.strval($authorization_header));

        $headers = [//Default headers
            'Content-Type: application/json',
            'Content-Length: '.strlen($dataString),
            'x-http-method-override: '.$method,
            'Authorization: '.$authorization_header,
        ];//End of $headers

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST', // V3.0 update, always using POST increases security
            CURLOPT_POSTFIELDS => $dataString,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // Solves windows compatibility
            CURLOPT_SSL_VERIFYHOST => false, // Solves windows compatibility
        ));//End of curl_setopt_array
        $error = curl_error($curl);
        if ($error) {
            self::log(self::INDENT.'CURL error:        ');
            print_r($error);
            throw new Error(500, null, 'CURL error', null);
        }//End of if
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if ($test == true) {
            return [
                'http_code' => $http_code,
                'content_type' => $content_type,
                'body' => json_decode($response, true),
            ];
        }//End of if

        if ($http_code == 200) {
            if (strpos($content_type, 'json') !== false) {
                $paybookResponse = json_decode($response, true);

                return $paybookResponse['response'];
            } else {
                return $response;
            }//End of if
        } else {
            if (strpos($content_type, 'json') !== false) {
                $paybookResponse = json_decode($response, true);
                throw new Error($http_code, $paybookResponse['response'], $paybookResponse['message'], $paybookResponse['status']);
            } else {
                throw new Error($http_code);
            }//End of if
        }//End of if
    }//End of __call

    public static function log($message, $test_init = false)
    {
        if (self::$log == true || $test_init == true) {
            echo $message.PHP_EOL;
        }//End of if
    }//End of log
}//End of Paybook class

// 	@staticmethod
// 	def __get_api_error__(conn):
// 		try:
// 			api_error = Error(conn.status_code,'','','')
// 		except Exception as e:
// 			api_error = Error(500,'Connection Error')
// 		return api_error

class Error extends Exception
{
    public function __construct($code, $response = '', $message = '', $status = '')
    {
        $this->code = $code;
        $this->response = $response;
        $this->message = $message;
        $this->status = $status;
    }//End of __construct

    public function get_message()
    {
        return $this->message;
    }//End of get_message

    public function get_code()
    {
        return $this->code;
    }//End of get_code

    public function get_array()
    {
        return [
            'code' => $this->code,
            'response' => $this->response,
            'message' => $this->message,
            'status' => $this->status,
        ];//End of return
    }//End of get_array
}//End of error
