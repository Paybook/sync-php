<?php

require __DIR__.'/vendor/autoload.php';

$api_key = '';
$id_user = '';
$id_credential = '';

function folder_exist($folder)
{
    $path = realpath($folder);

    return ($path !== false and is_dir($path)) ? $path : false;
}

/*
Inicializa el SDK con tu API key de Sync:
*/
paybook\Paybook::init($api_key);

/*
Crea el usuario con el que se descargarán las facturas:
*/
$user = new paybook\User(null, $id_user);

/*
Crea una sesión para el usuario seleccionado:
*/
$session = new paybook\Session($user);

/*
Obtiene las transacciones y archivos adjuntos sincronizadas para la credencial específica:
*/
$options = [
    'id_credential' => $id_credential,
    'limit' => 1,
];//End of $options
$attachments = paybook\Attachment::get($session, null, null, null, $options);

/*
En caso de que haya archivos adjuntos intenta descargarlos:
*/
if (count($attachments) > 0) {

    /*
    Itera sobre los archivos adjuntos:
    */
    foreach ($attachments as $key => $attachment) {

        /*
        Hace una petición a Sync para obtener el contenido de cada archivo adjunto:
        */
        $url = $attachment->url;
        $id_attachment = substr($url, 1, strlen($url));
        $attachment_content = paybook\Attachment::get($session, null, $id_attachment);

        /*
        Guarda el contenido en un archivo en tu computadora:
        */

        $invoice_example_name = 'factura_ejemplo.xml';

        if (!folder_exist('downloads')) {
            mkdir('downloads');
        }//End of if
        if (!folder_exist('downloads/example')) {
            mkdir('downloads/example');
        }//End of if

        $xml_file = fopen('downloads/example/'.$attachment->file, 'w') or die('No se pudo guardar archivo '.$attachment->file);
        fwrite($xml_file, $attachment_content);
        fclose($xml_file);

        echo PHP_EOL;
        echo ' Archivo '.$attachment->file.' descargado exitósamente. El archivo fue guardado en: '.PHP_EOL;
        echo PHP_EOL;
        echo '   -> '.__DIR__.'/downloads/example/'.$attachment->file;
        echo PHP_EOL;
        echo PHP_EOL;

        /*
        Forza que se termine el loop (esta línea se pude descomentar en caso de que se deseen descargar todos los archivos):
        */
        break;
    }//End of if
}//End of if
