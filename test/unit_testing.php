<?php
/*
Uncomment this to run it as external library:
// -------- */
require __DIR__.'/../vendor/autoload.php';

/*
Uncomment this to run it with source code:
$source_dir = __DIR__.'/../src/';
require $source_dir.'sync.php';
*/
define('API_KEY', '<api_key>');
define('WEBHOOK_ENDPOINT', '<your_webhook_endpoint>');
// --------

use Paybook\Sync\Sync;

function prettyJson($data) {
    echo json_encode($data, JSON_PRETTY_PRINT).PHP_EOL;
}

$tmp_id_user = null;
function main()
{
    try {
        // Consultar usarios
        $response = Sync::run(
            array("api_key" => API_KEY),
            '/users', 
            array(), 
            'GET'
        );
        echo "->Consultar usarios".PHP_EOL;
        print_r($response);

        // Consultar un usuario en especifico
        $response = Sync::run(
            array("api_key" => API_KEY),
            '/users', 
            array("id_user"=>'5df859c4a7a6442757726ef4'), 
            'GET'
        );
        echo "->Consultar un usuario en especifico".PHP_EOL;
        print_r($response);

        // Crear un Usuario
        $response = Sync::run(
            array("api_key" => API_KEY),
            '/users', 
            array(
                "id_external"=> 'MIST030794',
                "name"=> 'Rey Misterio'
            ), 
            'POST'
        );
        echo "->Crear un Usuario".PHP_EOL;
        print_r($response);
        $id_user = $response->id_user;
        $tmp_id_user = $id_user;

        // Actualizar un usuario
        $response = Sync::run(
            array("api_key" => API_KEY),
            "/users/$id_user", 
            array("name"=> 'Rey Misterio Jr.'), 
            'PUT'
        );
        echo "->Actualizar un usuario".PHP_EOL;
        print_r($response);
        
        // Obtener token de sesión
        $token = Sync::auth(
            array("api_key" => API_KEY),
            array("id_user"=>$id_user)
        );
        echo "->Obtener token de sesión".PHP_EOL;
        prettyJson($token);
        $tokenCode = $token['token'];
        
        // Verificar token de sesión
        $response = Sync::run(
            array(),
            "/sessions/$tokenCode/verify", 
            null,
            'GET'
        );
        echo "->Verificar token de sesión".PHP_EOL;
        prettyJson($response);

        // Consultar catálogos
        $response = Sync::run(
            $token,
            "/catalogues/sites", 
            array("limit"=>1),
            'GET'
        );
        echo "->Consultar catálogos".PHP_EOL;
        prettyJson($response);

        // Crear credenciales normal
        $payload = array("id_site"=>"5da784f1f9de2a06483abec1");
        $response = Sync::run(
            $token,
            "/catalogues/sites", 
            $payload,
            'GET'
        );
        $site = $response[0];
        $credentials = array();
        $credentials[$site->credentials[0]->name] = 'ACM010101ABC';
        $credentials[$site->credentials[1]->name] = 'test';
        $payload['credentials'] = $credentials;
        $response = Sync::run(
            $token,
            "/credentials", 
            $payload,
            'POST'
        );
        echo "->Crear credenciales normal".PHP_EOL;
        prettyJson($response);
        $satCredential = $response;
        sleep(30);

        // Consultar credenciales
        $response = Sync::run(
            $token,
            "/credentials", 
            null,
            'GET'
        );
        echo "->Consultar credenciales".PHP_EOL;
        prettyJson($response); 
        
        // Consulta status credenciales
        $id_job = $satCredential->id_job;
        $response = Sync::run(
            $token,
            "/jobs/$id_job/status", 
            null,
            'GET'
        );
        echo "->Consulta status credenciales".PHP_EOL;
        prettyJson($response);

        // Consultar Cuentas
        $response = Sync::run(
            $token,
            "/accounts", 
            array("id_credential"=>$satCredential->id_credential),
            'GET'
        );
        echo "->Consultar cuentas".PHP_EOL;
        prettyJson($response);

        // Consultar Transacciones
        $response = Sync::run(
            $token,
            "/transactions", 
            array(
                "id_credential"=>$satCredential->id_credential,
                "limit"=>1
            ),
            'GET'
        );
        echo "->Consultar transacciones".PHP_EOL;
        prettyJson($response);

        // Consultar el número de transacciones
        $response = Sync::run(
            $token,
            "/transactions/count", 
            array("id_credential"=>$satCredential->id_credential),
            'GET'
        );
        echo "->Consultar el número de transacciones".PHP_EOL;
        prettyJson($response);

        // Crear Webhook
        $response = Sync::run(
            array("api_key" => API_KEY),
            "/webhooks", 
            array(
                "url"=>WEBHOOK_ENDPOINT, 
                "events"=>array("credential_create","credential_update","refresh")
            ),
            'POST'
        );
        echo "->Crear Webhook".PHP_EOL;
        prettyJson($response);
        $id_webhook = $response->id_webhook;
        
        // Consultar Webhook
        $response = Sync::run(
            array("api_key" => API_KEY),
            "/webhooks", 
            null,
            'GET'
        );
        echo "->Consultar Webhook".PHP_EOL;
        prettyJson($response);
        sleep(150);

        // Eliminar Webhook
        $response = Sync::run(
            array("api_key" => API_KEY),
            "/webhooks/$id_webhook", 
            null,
            'DELETE'
        );
        echo "->Eliminar Webhook".PHP_EOL;
        prettyJson($response);
        
        // Consultar Archivos adjuntos
        $response = Sync::run(
            $token,
            "/attachments", 
            array(
                "id_credential"=>$satCredential->id_credential,
                "limit"=>1
            ),
            'GET'
        );
        echo "->Consultar Archivos adjuntos".PHP_EOL;
        prettyJson($response);

        // Obtener archivo adjunto
        $attachment = $response[0];
        $attachmentUrl = $attachment->url;
        $response = Sync::run(
            $token,
            $attachmentUrl, 
            null,
            'GET'
        );
        echo "->Obtener archivo adjunto".PHP_EOL;
        echo $response;

        // Obtener info extra
        $response = Sync::run(
            $token,
            $attachment->url."/extra", 
            null,
            'GET'
        );
        echo "->Obtener info extra".PHP_EOL;
        prettyJson($response);

        # --------------------- TWOFA --------------------------- #
        // Crear credenciales twofa
        $payload = array("id_site"=>"56cf5728784806f72b8b4569");
        $response = Sync::run(
            $token,
            "/catalogues/sites", 
            $payload,
            'GET'
        );
        
        $site = $response[0];
        $credentials = array();
        $credentials[$site->credentials[0]->name] = 'test';
        $credentials[$site->credentials[1]->name] = 'test';
        $payload['credentials'] = $credentials;
        $response = Sync::run(
            $token,
            "/credentials", 
            $payload,
            'POST'
        );
        echo "->Crear credenciales twofa".PHP_EOL;
        prettyJson($response);
        $twofaCredential = $response;
        sleep(20);

        $id_job = $twofaCredential->id_job;
        $response = Sync::run(
            $token,
            "/jobs/$id_job/status", 
            null,
            'GET'
        );
        echo "->Consulta status credenciales twofa".PHP_EOL;
        prettyJson($response);

        $is_twofa = False;
        if($response[sizeof($response)->code] == 410) {
            $is_twofa = True;
            echo "Is two-fa!".PHP_EOL;
        }
        
        // Manda TWOFA
        $twofaToken = array("twofa" => array());
        $twofaToken["twofa"][$response[2]->twofa[0]->name] = "123456";
        $twofa = Sync::run(
            $token,
            "/jobs/$id_job/twofa", 
            $twofaToken, 
            'POST'
        );
        echo "->Manda TWOFA".PHP_EOL;
        prettyJson($twofa);

        $response = Sync::run(
            $token,
            "/jobs/$id_job/status", 
            null,
            'GET'
        );
        echo "->Consulta nuevamente status credenciales twofa".PHP_EOL;
        prettyJson($response);

        # ------------------------- Eliminate --------------------- #

        // Eliminar credencial
        $id_credential = $satCredential->id_credential;
        $response = Sync::run(
            $token,
            "/credentials/$id_credential", 
            null,
            'DELETE'
        );
        echo "->Eliminar credencial".PHP_EOL;
        prettyJson($response);

        // Eliminar un usuario
        $response = Sync::run(
            array("api_key" => API_KEY),
            "/users/$id_user", 
            array(), 
            'DELETE'
        );
        echo "->Eliminar un usuario".PHP_EOL;
        print_r($response);

    } catch (Exception $error) {
        echo "------ TEST ERROR---------".PHP_EOL;
        if ($tmp_id_user) {
            // Eliminar un usuario
            $response = Sync::run(
                array("api_key" => API_KEY),
                "/users/$tmp_id_user", 
                array(), 
                'DELETE'
            );
            echo "Usuario eliminado".PHP_EOL;
            prettyJson($response);
            die();
        }
    }
} // END main

main();