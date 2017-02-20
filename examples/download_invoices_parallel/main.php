<?php

$time_start = microtime(true);

require __DIR__.'/vendor/autoload.php';

/*
Configuración del script
*/
$api_key = 'YOUR_API_KEY';
$id_user = 'SOME_ID_USER';
$id_credential = 'SOME_ID_CREDENTIAL';
$DOWNLOADS_DIR = '/usr/src/example/downloads';
$THREADS = 10;

class DownloadThread extends Thread
{
    const DOWNLOADS_DIR = '/usr/src/example/downloads';
    public $data;

    public function __construct($attachments, $session, $id)
    {
        $this->attachments = $attachments;
        $this->session = $session;
        $this->id = $id;
    }

    public function run()
    {
        $attachment_counter = 0;
        $session = $this->session;
        $DOWNLOADS_DIR = self::DOWNLOADS_DIR;

        foreach ($this->attachments as $key => $attachment) {

            /*
            Hace una petición a Sync para obtener el contenido de cada archivo adjunto:
            */
            $url = $attachment->url;
            $id_attachment = substr($url, 1, strlen($url));
            $attachment_content = paybook\Attachment::get($session, null, $id_attachment);

            /*
            Guarda el contenido en un archivo en tu computadora:
            */

            echo '      Hilo '.strval($this->id).' - Downloading file '.$attachment->file.'...'.PHP_EOL;
            $xml_file = fopen($DOWNLOADS_DIR.'/'.$attachment->file, 'w') or die('No se pudo guardar archivo '.$attachment->file.PHP_EOL);
            fwrite($xml_file, $attachment_content);
            fclose($xml_file);
        }
    }
}

/*
Crea el directorio de descarga:
*/
$path = realpath($DOWNLOADS_DIR);
if (!($path !== false and is_dir($path))) {
    mkdir($DOWNLOADS_DIR);
}

/*
Inicializa el SDK con tu API key de Sync:
*/
echo 'Inicializa el SDK ...'.PHP_EOL;
paybook\Paybook::init($api_key);

/*
Crea el usuario con el que se descargarán las facturas:
*/
echo 'Crea usuario ... '.PHP_EOL;
$user = new paybook\User(null, $id_user);

/*
Crea una sesión para el usuario seleccionado:
*/
echo 'Inicia sesión ... '.PHP_EOL;
$session = new paybook\Session($user);

/*
Obtiene las transacciones y archivos adjuntos sincronizadas para la credencial específica:
*/
$options = [
    'id_credential' => $id_credential,
    'limit' => 500,
];//End of $options
echo 'Consulta los archivos adjuntos ... '.PHP_EOL;
$attachments = paybook\Attachment::get($session, null, null, null, $options);
echo 'Adjuntos: '.strval(count($attachments)).PHP_EOL;

/*
En caso de que haya archivos adjuntos intenta descargarlos:
*/
if (count($attachments) > 0) {
    $thread_load = count($attachments) / $THREADS;

    $arrays = array_chunk($attachments, $thread_load + 1);

    /*
    Itera sobre los archivos adjuntos:
    */
    $thread_attachments = [];
    $id = 1;
    foreach ($arrays as $key => $thread_attachments) {
        echo '   Iniciando hilo '.strval($id).' de descarga para '.strval(count($thread_attachments)).' archivos adjuntos ... '.PHP_EOL;
        $stack[] = new DownloadThread($thread_attachments, $session, $id);
        ++$id;
    }

    foreach ($stack as $thread) {
        $thread->start();
    }
} else {
    echo 'No hay archivos adjuntos para descargar'.PHP_EOL;
}
