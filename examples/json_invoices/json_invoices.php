<?php

require __DIR__.'/vendor/autoload.php';

try {

    /*
    IMPORTANTE: remplaza el valor de tu API key y un ID de usuario de Sync donde se indica:
    */
    $api_key = 'YOUR_API_KEY';
    $id_user = 'SOME_SYNC_ID_USER';

    /*
    Inicializa algunas variables globales que se utilizarán:
    */

    $SAT_SITE_ORGANIZATION = '56cf4ff5784806152c8b4568';
    $XML_ATTACHMENT_TYPE = '56bcdfca784806d1378b4567';
    $FILE_NAME = 'facturas.json';

    /*
    Inicializa el SDK con tu API key de Sync:
    */

    $env = 'https://syncdev.paybook.com/v1/';
    paybook\Paybook::init($api_key, null, null, $env);
    // paybook\Paybook::init($api_key);

    /*
    Crea el usuario con el que se descargarán las facturas (únicamente instancía un usuario ya existente):
    */
    $user = new paybook\User(null, $id_user);

    /*
    Crea una sesión para el usuario seleccionado:
    */
    $session = new paybook\Session($user);

    /*
    Obtiene las transacciones del SAT en un periodo específico:
    */

    $options = [
        'id_site_organization' => $SAT_SITE_ORGANIZATION,
        'dt_transaction_from' => 1485907200, // 1ro de febrero
        'dt_transaction_to' => 1488326400, // 1ro de marzo
        'limit' => 5,
    ];

    $transactions = paybook\Transaction::get($session, null, $options);

    /*
    Si no hay transacciones del SAT termina la ejecución
    */

    if (count($transactions) == 0) {
        echo PHP_EOL;
        exit(' Tu usuario de Sync no cuenta con transacciones sincronizadas del SAT'.PHP_EOL.PHP_EOL);
    }

    /*
    Si hay transacciones del SAT itera sobre ellas y obtiene los XML parseados:
    */

    $xmls_parsed = [];
    $xmls_obtained = 0;

    echo PHP_EOL;

    foreach ($transactions as $i => $transaction) {

        /*
        Por cada transaccione del SAT checa si tiene archivos adjuntos (si no tiene se va a la sig. transacción)
        */
        if (is_null($transaction->attachments)) {
            continue;
        }

        /*
        Si la transacción tiene archivos adjuntos checa cuál de estos es XML
        */
        foreach ($transaction->attachments as $j => $attachment) {

            /*
            Si el archivo no es XML continua con el sig. archivo adjunto ligado a esta transacción del SAT
            */
            if ($attachment['id_attachment_type'] != $XML_ATTACHMENT_TYPE) {
                continue;
            }

            /*
            Si el archivo adjunto ex un XMl obtiene su versión parseada a JSON
            */

            $id_attachment = $attachment['id_attachment'];

            $xml_parsed = paybook\Attachment::get($session, null, $id_attachment, $extra = true);

            $xmls_obtained = $xmls_obtained + 1;

            echo '   '.strval($xmls_obtained).'. '.$attachment['file'].' obtenido'.PHP_EOL;
            array_push($xmls_parsed, $xml_parsed);
        }
    }

    if (count($xmls_parsed) == 0) {
        echo PHP_EOL;
        exit(' Las transacciones del SAT no cuenta con XMLs adjuntos'.PHP_EOL.PHP_EOL);
    }//End of if

    $json = [
        'facturas' => $xmls_parsed,
    ];
    $fp = fopen($FILE_NAME, 'w');
    fwrite($fp, json_encode($json, JSON_PRETTY_PRINT));
    fclose($fp);

    echo PHP_EOL;
    echo PHP_EOL;
    echo ' Las facturas fueron descargadas en formato JSON y guardadas en el archivo: '.PHP_EOL;
    echo PHP_EOL;
    echo '   '.__DIR__.'/'.$FILE_NAME.PHP_EOL;
    echo PHP_EOL;
    echo ' \0/ Script ejecutado exitósamente. Gracias por usar Sync'.PHP_EOL;
    echo PHP_EOL;
} catch (paybook\Error $e) {
    echo PHP_EOL;
    echo ' ¡Ups! Ocurrió un error, respuesta de Sync API:'.PHP_EOL;
    echo PHP_EOL;
    echo '   '.$e->get_code().' '.$e->get_message().PHP_EOL;
    echo PHP_EOL;
    echo ' Puedes consultar la lista de errores del Sync API en la siguiente liga:'.PHP_EOL;
    echo PHP_EOL;
    echo '   https://www.paybook.com/sync/docs/API#es&response'.PHP_EOL;
    echo PHP_EOL;
}//End of try
