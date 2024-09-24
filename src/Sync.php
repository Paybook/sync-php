<?php
namespace Paybook\Sync;
use Requests;
use Exception;
define('SYNC_API_URL', "https://sync.paybook.com/v1");

class Sync {
    public static function auth(array $AUTH, array $id_user)
    {
        try {
            $session = Sync::run($AUTH, '/sessions', $id_user, 'POST');
            if (isset($session->token)) {
                $response = array("token"=>$session->token);
                return $response;
            }else {
                throw new Error(
                    $session->code,
                    $session->response,
                    $session->message,
                    $session->satus
                );
            }
        } catch (Exception $error) {
            throw $error;
        }
    } // END of auth function

    public static function strictAuth()
    {
        echo "Stric auth mode ON!".PHP_EOL;
        # code...
    } // END of strictAuth function

    public static function run(array $AUTH, string $route, ?array $payload, string $method)
    {
        try {
            // BUILD URI
            $uri = SYNC_API_URL.$route;
            // SET HEADERS
            $headers = array(
                'Content-type' => "application/json"
            );
            // CHECK AUTH TYPE
            $authKey = key($AUTH);
            if ($authKey === 'api_key') {
                $headers['Authorization'] = "API_KEY api_key=".$AUTH[$authKey];
            } elseif ($authKey === 'token') {
                $headers['Authorization'] = "TOKEN token=".$AUTH[$authKey];
            }
            // ASSIGN HTTP METHOD
            switch ($method) {
                case 'GET':
                    $headers['X-Http-Method-Override'] = 'GET';
                break;
                case 'PUT':
                    $headers['X-Http-Method-Override'] = 'PUT';
                break;
                case 'DELETE':
                    $headers['X-Http-Method-Override'] = 'DELETE';
                    break;
            }
            $result = Requests::post($uri, $headers, json_encode($payload));
            $http_code = $result->status_code;
            if ($http_code >= 200 & $http_code <= 204) {
                $response = $result->body;
                if (!startsWith($response, '<?xml')) {
                    $response = json_decode($response);
                    $response = (is_array($response->response) ||  gettype($response->response) !== "boolean") ? $response->response : $response;
                    return $response;
                } else {
                    return $response;
                } // END if-else
            } else if ($result->body){ // Paybook responded succesfully
                $response = json_decode($result->body);
                throw new Error(
                    $response->code,
                    $response,
                    $response->message,
                    $response->status
                );
            } else {
                throw new Error(
                    $http_code,
                    $result,
                    "Something went wrong \n", 
                    null
                );
            }// END if-else-if
        } catch(Exception $error){
            errorHandler($error);
        } // END try-catch
    } // END run function
} // END Sync Class

function errorHandler($error) {
    if (!method_exists($error, 'get_code')) {
        print_r($error);
    } else {
        throw $error;
    }
} // END errorHandler function

function startsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
} // END startsWith function

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

    public function get_response()
    {
        return $this->response;
    } // End of get_response
}//END of class error
